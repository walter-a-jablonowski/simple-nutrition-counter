<?php

/*@

FoodRanking

Turns a NutrientReport into the short list the model gets to see. Deterministic - no
model is involved here either.

Three reasons the choice is made here and not in the prompt:

- 253 foods with 40 nutrients each does not fit in a prompt worth paying for, and the
  model would spend its effort on arithmetic instead of on judgement
- the ranking is testable without a model call
- a food that is not in the list cannot be recommended, so the model can never name
  something the user does not own

What is left to the model: which of the shortlisted foods actually go together, how to
say it, and whether a food fits the user's own rules.

Candidates are foods with a `type` only - the others carry no vitamin or mineral panel,
so their contribution to a deficit is unknown, not zero. The excess side has no such
restriction: it reads off what was eaten, and every food carries the values that side
uses. See dev_info/Nutrition_Advisor_Plan.md

*/
class FoodRanking  /*@*/
{
  // Eating one food more often than another is a goal of the bundle ("change some food
  // daily"), so a food already eaten in the range loses ground per logging. A divisor,
  // not a subtraction: the score is a density, and what it is worth per calorie does
  // not depend on how often it was eaten - only how much it is still worth suggesting

  const REPEAT_PENALTY = 0.15;

  /* The day holds only so many calories, so what counts is how much of a gap a food
     closes per calorie, not per portion. Without this a big portion always wins and
     the list fills up with the heaviest dishes.

     The floor keeps a food that is nearly calorie free from dividing by almost nothing.
     Nothing under it can be recommended in a useful amount anyway */

  const KCAL_BASE  = 100;   // the density is "per 100 kcal"
  const KCAL_FLOOR = 100;   // portions below this are scored as if they had it

  /* A nutrient that is a whisker over its bound is not a reason to avoid a food, one
     that is at five times its bound is. So what a food adds to an excess is weighted
     by how far over that nutrient already is, and only a share this size is worth
     naming at all - without it every protein food "raises" the amino acids that sit
     one percent over */

  const RAISE_FLOOR = 0.05;

  // Nutrients no food recommendation can fix, as "group.short".
  //
  // Water is short in the data because the user does not log what they drink, not
  // because their food is dry - coverage cannot see a missing entry, only missing food
  // data. Chasing it would put high water foods at the top of every list

  const IGNORED = ['misc.H2O'];

  // Foods listed per nutrient that is over its bound

  const MAX_CONTRIBUTORS = 5;

  // Panel-less foods named as the reason a group could not be measured

  const MAX_UNTYPED = 10;

  // Food fields that say something about whether a food should be recommended at all

  const FLAGS = ['state', 'acceptable', 'careful', 'noUseIf', 'limit', 'comment'];

  private SimpleData $layoutView;
  private SimpleData $combinedModel;
  private int        $maxFoods;


  public function __construct( SimpleData $layoutView, SimpleData $combinedModel, int $maxFoods = 40 )
  {
    $this->layoutView    = $layoutView;
    $this->combinedModel = $combinedModel;
    $this->maxFoods      = $maxFoods;
  }


  /*@

  select()

  Everything about foods the prompt needs, in one call so the rules that decide what
  counts as a deficit live in one place.

  ARGS: report, as NutrientReport::forRange() returns it

  RETURN:
    deficits:     nutrient rows worth acting on
    excesses:     nutrient rows over their upper bound
    candidates:   scored foods, best first, at most maxFoods
    contributors: excess nutrient short => the foods it came from
    untyped:      logged foods that carry no panel, biggest first

  */
  public function select( array $report ) : array  /*@*/
  {
    $deficits = $this->deficits( $report );
    $excesses = $this->excesses( $report );

    return [
      'deficits'     => $deficits,
      'excesses'     => $excesses,
      'candidates'   => $this->candidates( $report, $deficits, $excesses ),
      'contributors' => $this->contributors( $report, $excesses ),
      'untyped'      => $this->untyped( $report )
    ];
  }


  /*@

  The logged foods that carry no reference panel, most calories first.

  This is what turns the coverage number into something the user can act on: a group
  reads low because these foods contribute calories but no vitamins, and giving the
  biggest of them a `type` is what raises it. Names only - there is nothing to report
  about a food whose values are unknown

  */
  private function untyped( array $report ) : array  /*@*/
  {
    $foods = [];

    foreach( $report['byFood'] ?? [] as $name => $food )
      if( empty( $this->combinedModel->get("$name.type")))
        $foods[] = ['food' => $name, 'caloriesPerDay' => $food['calories'], 'eaten' => $food['count']];

    usort( $foods, fn( $a, $b) => $b['caloriesPerDay'] <=> $a['caloriesPerDay']);

    return array_slice( $foods, 0, self::MAX_UNTYPED);
  }


  /*@

  Nutrients the user is short of and a food could do something about.

  Unmeasurable ones are left out: their value is low because the foods carry no panel,
  and recommending against that would be answering a data gap with a carrot

  */
  private function deficits( array $report ) : array  /*@*/
  {
    return array_values( array_filter( $report['nutrients'], fn( $row ) =>
      $row['status'] === 'below'
      && $row['measurable']
      && $row['ideal'] > 0
      && ! in_array("$row[group].$row[short]", self::IGNORED)
    ));
  }


  /*@

  Nutrients over their upper bound.

  No coverage condition here, unlike the deficits: a group the entries only partly
  carry is under reported, never over reported, so a value above the bound is real
  whatever the coverage says

  */
  private function excesses( array $report ) : array  /*@*/
  {
    return array_values( array_filter( $report['nutrients'], fn( $row ) =>
      $row['status'] === 'above' && $row['upper'] > 0
    ));
  }


  /*@

  Score every food that carries a panel and keep the best.

    gain    = how much of each open gap one usual amount closes, capped at the gap
    penalty = what it adds to nutrients that are already over
    repeat  = how often it was eaten in the range

  The cap is what stops one food that is huge in a single nutrient from winning: past
  the gap the extra does nothing, and the food that covers three gaps beats it

  */
  private function candidates( array $report, array $deficits, array $excesses ) : array  /*@*/
  {
    $byFood = $report['byFood'] ?? [];
    $scored = [];

    foreach( $this->layoutView->all() as $name => $amounts )
    {
      if( empty( $this->combinedModel->get("$name.type")) || ! is_array($amounts) || ! $amounts )
        continue;   // no reference panel, so its contribution is unknown, not zero

      $amountKey = array_keys($amounts)[ intdiv( count($amounts), 2)];   // the middle one
      $portion   = $amounts[$amountKey];

      $closes = [];
      $gain   = 0.0;

      foreach( $deficits as $row )
      {
        $value = $this->valueOf( $portion, $row );

        if( $value <= 0 )
          continue;

        $closes[ $row['nutrient']] = $value;
        $gain += min( $value, $row['gap']) / $row['gap'];
      }

      if( ! $closes )
        continue;   // closes nothing, so it has no business in the list

      $raises  = [];
      $penalty = 0.0;

      foreach( $excesses as $row )
      {
        $value = $this->valueOf( $portion, $row );
        $share = $value / $row['upper'];

        if( $value <= 0 || $share < self::RAISE_FLOOR )
          continue;

        $raises[ $row['nutrient']] = $value;
        $penalty += $share * ($row['over'] / $row['upper']);   // weighted by how far over it is
      }

      $count = $byFood[$name]['count'] ?? 0;

      $scored[] = [
        'food'     => $name,
        'vendor'   => $this->vendorOf( $name ),
        'amount'   => str_replace('_', '.', $amountKey),   // the key escapes the dot, see LayoutView
        'weight'   => $portion['weight'],
        'calories' => $portion['calories'],
        'price'    => $portion['price'],
        'amounts'  => $this->combinedModel->get("$name.usedAmounts") ?: [],
        'eaten'    => $count,
        'score'    => $this->score( $gain - $penalty, $portion['calories'], $count ),
        'closes'   => $closes,
        'raises'   => $raises,
        'flags'    => $this->flagsOf( $name )
      ];
    }

    usort( $scored, fn( $a, $b) => $b['score'] <=> $a['score']);

    return array_slice( $scored, 0, $this->maxFoods);
  }


  /*@

  The foods behind every nutrient that is over its bound, largest share first. Same
  question the nutrients tab answers when a row is tapped.

  Over all foods, not just the ones with a panel: this reads what was actually eaten

  */
  private function contributors( array $report, array $excesses ) : array  /*@*/
  {
    $byFood = $report['byFood'] ?? [];
    $result = [];

    foreach( $excesses as $row )
    {
      $foods = [];

      foreach( $byFood as $name => $food )
      {
        $value = $food['sums'][ $row['group']][ $row['short']] ?? 0;

        if( $value > 0 )
          $foods[] = ['food' => $name, 'perDay' => $value, 'eaten' => $food['count']];
      }

      usort( $foods, fn( $a, $b) => $b['perDay'] <=> $a['perDay']);

      $result[ $row['short']] = array_slice( $foods, 0, self::MAX_CONTRIBUTORS);
    }

    return $result;
  }


  /*@

  What one portion is worth: gaps closed per 100 kcal, less what it was already eaten.

  A food whose panel is only borrowed from a food default (`state: unprecise`) is not
  scored down here. Its numbers are rough, but they are the numbers the app has, and
  the data gets better as it is filled in - a permanent handicap would keep foods out
  of the list long after their panel was fixed. The flag travels with the candidate,
  so the model can say so.

  A food that is worth nothing or worse keeps its raw net, so it still sorts to the
  bottom - dividing a negative would turn "brings more harm than good" into a number
  that grows as the portion grows

  */
  private function score( float $net, float $calories, int $eaten ) : float  /*@*/
  {
    if( $net <= 0 )
      return round( $net, 3);

    $density = $net / max( $calories, self::KCAL_FLOOR) * self::KCAL_BASE;

    return round( $density / (1 + self::REPEAT_PENALTY * $eaten), 3);
  }


  /*@

  One nutrient of one amount. The grid keys the groups by their short, the same way a
  day entry does, so a report row points straight at it.

  The fallback is for sugar: CombinedModel duplicates fibre and salt into their groups
  but not sugar, so a food's sugar only lives in nutritionalValues - where every food
  has it, because it is printed on the pack. No nutrient short collides with the names
  in there, so the fallback can never pick up the wrong value

  */
  private function valueOf( array $portion, array $row ) : float  /*@*/
  {
    return (float) ($portion[ $row['group']][ $row['short']]
                 ?? $portion['nutriVal'][ $row['short']]
                 ?? 0);
  }


  // Placeholders in the data would offer the model a shop that does not exist,
  // same rule as MainController.foodVocabulary()

  private function vendorOf( string $name ) : string
  {
    $vendor = (string) ($this->combinedModel->get("$name.vendor") ?: '');

    return in_array( strtolower($vendor), ['none', 'multiple']) ? '' : $vendor;
  }


  // Only the flags a food actually sets, so the prompt carries no empty fields

  private function flagsOf( string $name ) : array
  {
    $flags = [];

    foreach( self::FLAGS as $flag )
    {
      $value = $this->combinedModel->get("$name.$flag");

      if( ! empty($value))
        $flags[$flag] = $value;
    }

    return $flags;
  }
}

?>
