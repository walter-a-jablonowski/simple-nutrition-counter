<?php

require_once 'lib/ai/GeminiClient.php';

/*@

NutritionAdvisor

The one part of the advisor a model does. Everything it is given is already worked out:
NutrientReport says what was eaten against the targets, FoodRanking says which foods are
worth looking at. What is left is judgement - which of them fit the user's own rules,
which go together, and how to say it in two sentences.

The prompt gets tables, not entries. The model never adds anything up, so its numbers
can not disagree with the app's.

Nothing here is saved and nothing is acted on: the answer goes to the panel, and the user
decides what to log. See dev_info/Nutrition_Advisor_Plan.md

*/
class NutritionAdvisor  /*@*/
{
  const PROMPT_FILE   = 'data/advisor/analysis_prompt.md';
  const DEFAULT_MODEL = 'gemini-3.6-flash';   // must support generateContent, see config.yml

  // Warmer than the photo import, which transcribes. This one writes prose and picks
  // between foods that score nearly the same, and 0 makes that read mechanical

  const TEMPERATURE = 0.4;
  const MAX_TOKENS  = 8192;   // six sections of prose plus the model's thinking


  /*@

  analyse()

  ARGS:
    reports:   one NutrientReport per range, longest last (the one that is judged)
    selection: FoodRanking::select() of the longest range
    rules:     the bundle's diet rules, markdown

  RETURN: ['advice' => ..., 'warnings' => [...]]

  Throws with a user-facing message when the model can not be reached or answers
  something unusable.

  */
  public static function analyse( array $reports, array $selection, string $rules ) : array  /*@*/
  {
    $answer = GeminiClient::ask(
      config::get('advisor.model') ?: self::DEFAULT_MODEL,
      file_get_contents( self::PROMPT_FILE ),
      self::userText( $reports, $selection, $rules ),
      [],                      // no pictures, this one only reads numbers
      self::responseSchema(),
      ['temperature' => self::TEMPERATURE, 'maxOutputTokens' => self::MAX_TOKENS]);

    if( config::get('advisor.debug'))
      error_log('NutritionAdvisor: ' . json_encode( $answer ));

    return self::fromAnswer( $answer, self::knownFoods( $selection ));
  }


  /*@

  Model answer -> what the panel shows.

  Public because it is the seam the tests use: a recorded answer replays the whole
  mapping without calling the model (see tools/test_advisor.php)

  ARGS:
    data:  the decoded answer
    known: every food name the answer may mention

  */
  public static function fromAnswer( array $data, array $known ) : array  /*@*/
  {
    $warnings = [];

    $advice = [
      'summary'     => trim( (string) ($data['summary'] ?? '')),
      'dataNotes'   => self::strings( $data['dataNotes'] ?? []),
      'deficits'    => self::notes( $data['deficits'] ?? [], 'nutrient'),
      'excesses'    => self::notes( $data['excesses'] ?? [], 'nutrient'),
      'recommended' => self::foods( $data['recommended'] ?? [], $known, $warnings),
      'avoid'       => self::foods( $data['avoid'] ?? [], $known, $warnings)
    ];

    if( $advice['summary'] === '')
      $warnings[] = 'The model returned no summary.';

    return ['advice' => $advice, 'warnings' => $warnings];
  }


  /*@

  Every food name the answer is allowed to use: the shortlist it was given, plus what
  the user actually logged. Anything else is a hallucination and is dropped rather
  than shown - a food the user does not own is worse than one suggestion fewer

  */
  public static function knownFoods( array $selection ) : array  /*@*/
  {
    $known = array_column( $selection['candidates'] ?? [], 'food');

    foreach( $selection['contributors'] ?? [] as $foods )
      $known = array_merge( $known, array_column( $foods, 'food'));

    $known = array_merge( $known, array_column( $selection['untyped'] ?? [], 'food'));

    return array_values( array_unique( $known ));
  }


  /*@

  The prompt body. Tables rather than json: it is a third of the tokens and the model
  reads it the way the user would

  */
  private static function userText( array $reports, array $selection, string $rules ) : string  /*@*/
  {
    $primary = end( $reports );   // the longest range, the one the shortlist was built from
    $text    = [];

    $text[] = '# What was eaten';
    $text[] = '';
    $text[] = self::rangeLine( $reports );
    $text[] = '';
    $text[] = self::nutrientTable( $reports );

    if( $notMeasurable = self::notMeasurable( $primary ))
    {
      $text[] = '';
      $text[] = '## Not measurable';
      $text[] = '';
      $text[] = $notMeasurable;
    }

    if( $selection['untyped'] )
    {
      $text[] = '';
      $text[] = '## Foods without a reference panel';
      $text[] = '';
      $text[] = 'These carry calories but no vitamins or minerals, which is what holds the';
      $text[] = 'coverage down. Most calories first.';
      $text[] = '';

      foreach( $selection['untyped'] as $food )
        $text[] = "- {$food['food']} — {$food['caloriesPerDay']} kcal/day, eaten {$food['eaten']}x";
    }

    $text[] = '';
    $text[] = '## Daily totals';
    $text[] = '';

    foreach( $primary['macros'] as $name => $value )
      $text[] = "- $name: $value";

    if( $selection['excesses'] )
    {
      $text[] = '';
      $text[] = '## Where the excess came from';
      $text[] = '';

      foreach( $selection['excesses'] as $row )
      {
        $from = [];

        foreach( $selection['contributors'][ $row['short']] ?? [] as $food )
          $from[] = "{$food['food']} ({$food['perDay']} {$row['unit']}/day, {$food['eaten']}x)";

        $text[] = "- {$row['nutrient']}: " . ($from ? implode(', ', $from) : 'no single food stands out');
      }
    }

    $text[] = '';
    $text[] = '# Foods to choose from';
    $text[] = '';
    $text[] = 'Ranked by how much of the open gaps one usual amount closes per calorie.';
    $text[] = 'Nothing outside this list may be recommended.';
    $text[] = '';
    $text[] = self::candidateList( $selection['candidates'] );

    $text[] = '';
    $text[] = "# The user's own rules";
    $text[] = '';
    $text[] = 'Their diet, in their own words. A food that these rule out does not belong in';
    $text[] = 'the recommendations however well it scores.';
    $text[] = '';
    $text[] = trim( $rules );

    return implode("\n", $text);
  }


  private static function rangeLine( array $reports ) : string
  {
    $parts = [];

    foreach( $reports as $report )
      $parts[] = "{$report['range']}: {$report['daysWithData']} of {$report['days']} days logged";

    return implode(', ', $parts) . '. Values are daily averages.';
  }


  /* One row per nutrient, both ranges side by side. Unmeasurable rows are left out
     here and listed on their own, so the table holds only numbers worth reading */

  private static function nutrientTable( array $reports ) : string
  {
    $primary = end( $reports );
    $keys    = array_keys( $reports );
    $head    = [];

    foreach( $reports as $report )
      $head[] = $report['range'];

    $rows = ['| nutrient | unit | ' . implode(' | ', $head) . ' | target | ok range | status | cov |',
             '|---|---|' . str_repeat('---|', count($reports)) . '---|---|---|---|'];

    foreach( $primary['nutrients'] as $i => $row )
    {
      if( ! $row['measurable'] )
        continue;

      $values = [];

      foreach( $keys as $k )
        $values[] = self::num( $reports[$k]['nutrients'][$i]['perDay'] ?? 0);

      $rows[] = "| {$row['nutrient']} | {$row['unit']} | " . implode(' | ', $values)
              . ' | ' . self::num( $row['ideal'])
              . ' | ' . self::num( $row['lower']) . '-' . self::num( $row['upper'])
              . " | {$row['status']} | {$row['coverage']}% |";
    }

    return implode("\n", $rows);
  }


  /* What could not be measured. A whole group is named on its own, but a group where
     only some nutrients are out names those - fibre and sugar sit in the same group and
     do not share a coverage, so "Carbs" alone would write off a value that is fine */

  private static function notMeasurable( array $report ) : string
  {
    $groups = [];

    foreach( $report['nutrients'] as $row )
    {
      $group = $row['groupName'];

      $groups[$group]['total'] = ($groups[$group]['total'] ?? 0) + 1;

      if( ! $row['measurable'] )
      {
        $groups[$group]['out'][] = $row['nutrient'];
        $groups[$group]['cov']   = $row['coverage'];
      }
    }

    $lines = [];

    foreach( $groups as $name => $group )
    {
      if( empty( $group['out']))
        continue;

      $what = count( $group['out']) === $group['total'] ? $name : "$name (" . implode(', ', $group['out']) . ')';

      $lines[] = "- $what: only {$group['cov']} % of the calories carried these values";
    }

    return implode("\n", $lines);
  }


  private static function candidateList( array $candidates ) : string
  {
    $lines = [];

    foreach( $candidates as $food )
    {
      $line = "- **{$food['food']}**";

      if( $food['vendor'] )
        $line .= " ({$food['vendor']})";

      $line .= "  amount: {$food['amount']}, {$food['calories']} kcal";

      if( $food['amounts'] )
        $line .= '  other amounts: ' . implode(' | ', $food['amounts']);

      $lines[] = $line;
      $lines[] = '  - closes: ' . self::pairs( $food['closes']);

      if( $food['raises'] )
        $lines[] = '  - raises: ' . self::pairs( $food['raises']);

      if( $food['eaten'] )
        $lines[] = "  - eaten {$food['eaten']}x in the range";

      foreach( $food['flags'] as $flag => $value )
        $lines[] = "  - $flag: " . (is_bool($value) ? ($value ? 'yes' : 'no') : $value);
    }

    return implode("\n", $lines);
  }


  private static function pairs( array $values ) : string
  {
    $parts = [];

    foreach( $values as $name => $value )
      $parts[] = "$name " . self::num( $value );

    return implode(', ', $parts);
  }


  // Small values keep their decimals, big ones do not need any

  private static function num( $value ) : string
  {
    $value = (float) $value;

    if( $value == 0 )     return '0';
    if( abs($value) >= 10 ) return (string) round( $value );
    if( abs($value) >= 1 )  return (string) round( $value, 1);

    return rtrim( rtrim( number_format( $value, 5, '.', ''), '0'), '.');
  }


  /* What the model may answer. Every field required, so it can not quietly leave a
     section out, and arrays rather than prose so the panel can lay them out */

  private static function responseSchema() : array
  {
    $note = [
      'type'             => 'object',
      'propertyOrdering' => ['nutrient', 'comment'],
      'properties'       => [
        'nutrient' => ['type' => 'string', 'description' => 'Nutrient name exactly as in the table'],
        'comment'  => ['type' => 'string', 'description' => 'One sentence, plain language, no diagnosis']
      ],
      'required' => ['nutrient', 'comment']
    ];

    $food = [
      'type'             => 'object',
      'propertyOrdering' => ['food', 'amount', 'because', 'comment'],
      'properties'       => [
        'food'    => ['type' => 'string', 'description' => 'Exactly as spelled in the list given'],
        'amount'  => ['type' => 'string', 'description' => 'One of that food\'s own amounts'],
        'because' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Nutrient names it helps with'],
        'comment' => ['type' => 'string', 'description' => 'One short sentence']
      ],
      'required' => ['food', 'amount', 'because', 'comment']
    ];

    $properties = [
      'summary'     => ['type' => 'string', 'description' => 'Two or three plain sentences, no markdown'],
      'dataNotes'   => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Where the data, not the food, is the problem'],
      'deficits'    => ['type' => 'array', 'items' => $note],
      'excesses'    => ['type' => 'array', 'items' => $note],
      'recommended' => ['type' => 'array', 'items' => $food],
      'avoid'       => ['type' => 'array', 'items' => $food]
    ];

    return [
      'type'             => 'object',
      'propertyOrdering' => array_keys( $properties ),
      'properties'       => $properties,
      'required'         => array_keys( $properties )
    ];
  }


  private static function strings( $list ) : array
  {
    $out = [];

    foreach( is_array($list) ? $list : [] as $item )
      if( is_string($item) && trim($item) !== '')
        $out[] = trim( $item );

    return $out;
  }


  private static function notes( $list, string $key ) : array
  {
    $out = [];

    foreach( is_array($list) ? $list : [] as $item )
    {
      $name = trim( (string) ($item[$key] ?? ''));

      if( $name !== '')
        $out[] = [$key => $name, 'comment' => trim( (string) ($item['comment'] ?? ''))];
    }

    return $out;
  }


  /* Food rows, dropping every name that was not in the lists the model was given.

     This is the guarantee the whole shortlist exists for: a recommendation the user
     can not tap is worse than one recommendation fewer, and a name the app does not
     know would break logging it */

  private static function foods( $list, array $known, array &$warnings ) : array
  {
    $out   = [];
    $index = array_combine( array_map('mb_strtolower', $known), $known) ?: [];

    foreach( is_array($list) ? $list : [] as $item )
    {
      $name = trim( (string) ($item['food'] ?? ''));

      if( $name === '')
        continue;

      $match = $index[ mb_strtolower($name)] ?? null;

      if( $match === null )
      {
        $warnings[] = "The model named a food that does not exist: \"$name\".";
        continue;
      }

      $out[] = [
        'food'    => $match,   // the app's spelling, so the panel can find it in the grid
        'amount'  => trim( (string) ($item['amount'] ?? '')),
        'because' => self::strings( $item['because'] ?? []),
        'comment' => trim( (string) ($item['comment'] ?? ''))
      ];
    }

    return $out;
  }
}

?>
