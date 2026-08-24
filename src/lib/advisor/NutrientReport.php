<?php

use Symfony\Component\Yaml\Yaml;

require_once 'lib/helper.php';
require_once 'models/functions.php';

/*@

NutrientReport

What the user actually ate over a time range, held against the targets of the nutrients
tab. Deterministic - no model is involved here.

The advisor sends this table instead of the raw entries: the numbers stay the app's own,
the prompt stays small, and the model spends its effort on choosing foods rather than on
adding up ten thousand values.

**Coverage is the point of this class.** A vitamin sum of zero means one of two very
different things - the user had none, or none of the foods they logged carries a panel -
and the second is the common one: only foods with a `type` have vitamins and minerals at
all. So every group reports the share of the range's calories its values were measured
from, and a group below the threshold is marked unmeasurable instead of being read as a
deficit.

The sums are never scaled up to compensate. The foods that carry no panel are coffee,
water and small items, so extrapolating from the measured share would invent nutrients
the user never ate.

Coverage also catches the two format boundaries in the stored days by itself: entries
written before 2026-08-16 carry no `sugar` and no `misc`, so those days lower the coverage
of exactly the groups they are missing. See dev_info/Nutrition_Advisor_Plan.md

*/
class NutrientReport  /*@*/
{
  // Nutrient groups as a day entry keys them - the group's `short`, see
  // MainController.#entryFromButton()

  const GROUP_SHORTS = ['fat', 'amino', 'vit', 'min', 'sec', 'misc'];

  // Fibre and sugar are the exception: they live at the top level of the entry, not
  // inside a group. Same special case as MainController.#renderNutrientRows()

  const TOP_LEVEL = ['carbs' => ['fibre', 'sugar']];

  // Macros read off the tsv columns. Every food has these, they come from the packaging

  const MACRO_COLUMNS = ['calories', 'fat', 'carbs', 'amino', 'salt', 'price'];

  const FOOD_TYPES = ['F', 'FE', 'S', 'M'];   // entry types that count as eaten

  /* Nutrients the day file also holds as a column, "group.short" => column.

     Salt is in the entry twice: as the tsv column every food fills from its packaging,
     and inside the mineral group, which only foods with a `type` carry (CombinedModel
     copies nutritionalValues.salt into minerals.Salt). Same number, but the column is
     complete and the group is not - read from the group it looks like the user eats
     half the salt they do. The column wins, and its coverage is always full */

  const MACRO_TWINS = ['min.NaCl' => 'salt'];

  private SimpleData $nutrientsView;
  private array      $captions;
  private string     $daysDir;
  private int        $minCoverage;


  /*@

  ARGS:
    nutrientsView: the bounds table of the nutrients tab, see NutrientsView
    captions:      group short => display name, filled by the same method
    userId:        whose days to read
    minCoverage:   percent of the calories a group needs before its values count

  */
  public function __construct( SimpleData $nutrientsView, array $captions, string $userId, int $minCoverage = 40 )  /*@*/
  {
    $this->nutrientsView = $nutrientsView;
    $this->captions      = $captions;
    $this->daysDir       = "data/users/$userId/days";
    $this->minCoverage   = $minCoverage;
  }


  /*@

  forRange()

  ARGS:
    range:  range key, see range_dates()
    anchor: the date the app shows, the range is anchored to it

  RETURN: the report, or null if the range key is unknown

  */
  public function forRange( string $range, string $anchor ) : ?array  /*@*/
  {
    $dates = range_dates( $range, $anchor );

    if( $dates === null )
      return null;

    $totals   = $this->readRange( $dates );
    $days     = count( $dates );
    $coverage = $this->coverage( $totals );

    return [
      'range'        => $range,
      'days'         => $days,
      'daysWithData' => $totals['daysWithData'],
      'coverage'     => $coverage,
      'macros'       => $this->macros( $totals, $days ),
      'nutrients'    => $this->nutrients( $totals, $coverage, $days ),
      'byFood'       => $this->byFood( $totals['byFood'], $days )
    ];
  }


  /*@

  Where the values came from: one entry per food, its nutrients as daily averages like
  everything else, and how often it was logged.

  The advisor needs both sides of it - the foods behind a nutrient that is over its
  bound, and how often a candidate was eaten already (the bundle's own goal is to vary
  the food, see FoodRanking).

  `count` is the raw number of entries, not an average: "four times last week" is what
  the user recognises.

  */
  private function byFood( array $byFood, int $days ) : array  /*@*/
  {
    $out = [];

    foreach( $byFood as $name => $food )
    {
      $sums = [];

      foreach( $food['sums'] as $group => $nutrients )
        foreach( $nutrients as $short => $value )
          $sums[$group][$short] = $days ? round( $value / $days, 5) : 0.0;

      $out[$name] = [
        'count'    => $food['count'],
        'calories' => $days ? round( $food['calories'] / $days, 1) : 0.0,
        'sums'     => $sums
      ];
    }

    return $out;
  }


  /*@

  Sum the range up per food, and count the calories each group was measured from.

  Per food rather than straight into one total, because the range totals are only half
  of what the advisor needs - which foods a value came from is the other half, and one
  pass gives both. The totals fall out of the same numbers, see rollUp().

  An entry counts as measured for a group when it carries that group's data: a
  non-empty array for a real group, the key itself for fibre and sugar - an entry
  written before those existed has no key at all, while `fibre: 0` is the food's
  answer and counts.

  */
  private function readRange( array $dates ) : array  /*@*/
  {
    $byFood       = [];
    $daysWithData = 0;

    foreach( $dates as $date )
    {
      $entries = read_day_file("{$this->daysDir}/$date.tsv")['entries'];
      $counted = false;

      foreach( $entries as $entry )
      {
        if( ! in_array( $entry['type'], self::FOOD_TYPES))
          continue;

        $blob = Yaml::parse( $entry['nutrients'] ?? '');

        if( ! is_array($blob))
          continue;   // empty or broken column: the entry carries no nutrients

        $counted  = true;
        $name     = $entry['food'];
        $calories = (float) ($entry['calories'] ?? 0);

        $food = $byFood[$name] ?? ['count' => 0, 'calories' => 0, 'macros' => [], 'sums' => [], 'measured' => []];

        $food['count']++;
        $food['calories'] += $calories;

        foreach( self::MACRO_COLUMNS as $column )
          $food['macros'][$column] = ($food['macros'][$column] ?? 0) + (float) ($entry[$column] ?? 0);

        // Groups

        foreach( self::GROUP_SHORTS as $group )
        {
          if( empty( $blob[$group]))
            continue;

          $food['measured'][$group] = ($food['measured'][$group] ?? 0) + $calories;

          foreach( $blob[$group] as $short => $value )
            if( is_numeric($value))
              $food['sums'][$group][$short] = ($food['sums'][$group][$short] ?? 0) + (float) $value;
        }

        // Fibre and sugar, from the top level of the entry. Counted one by one, not as
        // a group: they were added to the entry at different times, so an old entry
        // carries fibre but no sugar and only sugar's coverage may drop for it

        foreach( self::TOP_LEVEL as $group => $shorts )
          foreach( $shorts as $short )
            if( isset( $blob[$short]) && is_numeric( $blob[$short]))
            {
              $key = "$group.$short";

              $food['sums'][$group][$short] = ($food['sums'][$group][$short] ?? 0) + (float) $blob[$short];
              $food['measured'][$key]       = ($food['measured'][$key] ?? 0) + $calories;
            }

        $byFood[$name] = $food;
      }

      if( $counted )
        $daysWithData++;
    }

    return $this->rollUp( $byFood ) + ['byFood' => $byFood, 'daysWithData' => $daysWithData];
  }


  // The range totals are the per food numbers added up, nothing more

  private function rollUp( array $byFood ) : array
  {
    $totals = ['sums' => [], 'measured' => [], 'macros' => [], 'calories' => 0];

    foreach( $byFood as $food )
    {
      $totals['calories'] += $food['calories'];

      foreach( $food['macros'] as $column => $value )
        $totals['macros'][$column] = ($totals['macros'][$column] ?? 0) + $value;

      foreach( $food['measured'] as $key => $value )
        $totals['measured'][$key] = ($totals['measured'][$key] ?? 0) + $value;

      foreach( $food['sums'] as $group => $nutrients )
        foreach( $nutrients as $short => $value )
          $totals['sums'][$group][$short] = ($totals['sums'][$group][$short] ?? 0) + $value;
    }

    return $totals;
  }


  /*@

  Percent of the range's calories each group was measured from. Keyed by group short,
  plus one key per top level nutrient ("carbs.fibre"), see coverageKey()

  */
  private function coverage( array $totals ) : array  /*@*/
  {
    $keys = self::GROUP_SHORTS;

    foreach( self::TOP_LEVEL as $group => $shorts )
      foreach( $shorts as $short )
        $keys[] = "$group.$short";

    $coverage = [];

    foreach( $keys as $key )
      $coverage[$key] = $totals['calories'] > 0
                      ? (int) round(($totals['measured'][$key] ?? 0) / $totals['calories'] * 100)
                      : 0;

    return $coverage;
  }


  // Fibre and sugar carry their own coverage, the other nutrients that of their group

  private function coverageKey( string $group, string $short ) : string
  {
    return isset( self::TOP_LEVEL[$group]) ? "$group.$short" : $group;
  }


  /*@

  One row per nutrient of the nutrients tab, as a daily average against the same
  bounds the tab prints - so the panel and the tab can never disagree.

  A nutrient of an unmeasurable group keeps its value but carries `measurable: false`,
  which is what stops it being read as a deficit.

  */
  private function nutrients( array $totals, array $coverage, int $days ) : array  /*@*/
  {
    $rows = [];

    foreach( $this->nutrientsView->all() as $group => $nutrients )
    {
      foreach( $nutrients as $short => $def )
      {
        $twin    = self::MACRO_TWINS["$group.$short"] ?? null;
        $covered = $twin ? 100 : ($coverage[ $this->coverageKey( $group, $short )] ?? 0);

        $sum = $twin ? ($totals['macros'][$twin] ?? 0)
                     : ($totals['sums'][$group][$short] ?? 0);

        $perDay = $days ? round( $sum / $days, 5) : 0.0;

        if( $perDay < $def['lower'] )      $status = 'below';
        elseif( $perDay > $def['upper'] )  $status = 'above';
        else                               $status = 'ok';

        $rows[] = [
          'group'      => $group,
          'groupName'  => $this->captions[$group] ?? $group,
          'nutrient'   => $def['displayName'] ?: $def['name'],
          'short'      => $short,
          'unit'       => $def['unit'],
          'perDay'     => $perDay,
          'lower'      => $def['lower'],
          'ideal'      => $def['ideal'],
          'upper'      => $def['upper'],
          'status'     => $status,
          'gap'        => $status === 'below' ? round( $def['ideal'] - $perDay, 5) : null,
          'over'       => $status === 'above' ? round( $perDay - $def['upper'], 5) : null,
          'coverage'   => $covered,
          'measurable' => $covered >= $this->minCoverage
        ];
      }
    }

    return $rows;
  }


  /*@

  Daily averages of the values every food carries, so these need no coverage note.
  Water comes out of the misc group and fibre and sugar out of the top level, the
  rest are tsv columns.

  Eating time is not here on purpose: it is a widget value, and no food the advisor
  could recommend would change it.

  */
  private function macros( array $totals, int $days ) : array  /*@*/
  {
    $macros = [];

    foreach( self::MACRO_COLUMNS as $column )
      $macros[$column] = $days ? round(($totals['macros'][$column] ?? 0) / $days, 2) : 0.0;

    foreach( ['fibre', 'sugar'] as $short )
      $macros[$short] = $days ? round(($totals['sums']['carbs'][$short] ?? 0) / $days, 2) : 0.0;

    $macros['water'] = $days ? round(($totals['sums']['misc']['H2O'] ?? 0) / $days, 2) : 0.0;

    return $macros;
  }
}

?>
