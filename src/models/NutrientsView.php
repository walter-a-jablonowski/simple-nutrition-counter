<?php

/*@

NutrientsView

view for the nutrients tab

- pre calc nutrients (tab 2) recommended amounts per day
- easy print in html, less js logic

*/
trait NutrientsView  /*@*/
{
  protected SimpleData $nutrientsView;
  protected array      $widgetRanges = [];   // quick summary widgets, see makeWidgetRanges()


  /*@
  
  makeNutrientsView()
  
  */
  private function makeNutrientsView()  /*@*/
  {
    $this->nutrientsView = new SimpleData();

    foreach( self::NUTRIENT_GROUPS as $groupName )
    {
      // $shortName = $this->nutrientsModel->get("$groupName.short");
      // $this->captions[$shortName] = $this->nutrientsModel->get("$groupName.name");

      $group     = $this->nutrientsModel->get($groupName);
      $shortName = $group['short'];

      $this->captions[$shortName] = $group['name'];

      // foreach( $this->nutrientsModel->get("$groupName.substances") as $name => $attr )  // short is used as id
      foreach( $group['substances'] as $name => $attr )  // short is used as id
      {
        $a = $attr['amounts'][0];  // currently one hard coded person type
        
        $this->nutrientsView->set("$shortName.$attr[short]", [
          'name'        => $name,       // TASK: (advanced) currently using first entry only
          'displayName' => $attr['displayName'] ?? null,
          // 'unit'     => $attr['unit'] ?? 'mg',
          'unit'        => $attr['unit'] ?? $group['defaultUnit'] ?? 'mg',
          'group'       => $groupName,  // calc acceptable nutrient intake ideal with tolerance
          'lower'       => $this->calculateBound( $a['amount'], $a['lower'], false),
          'ideal'       => $a['amount'],
          'upper'       => $this->calculateBound( $a['amount'], $a['upper'], true)
        ]);
      }
    }
  }

  /*@

  makeWidgetRanges()

  Bounds for the signal color of the quick summary widgets (green inside, red above).
  Most of them use the same amounts as the nutrients tab, but on group level. Price
  and eating time are no nutrients, they live in the user settings

  */
  private function makeWidgetRanges()  /*@*/
  {
    // Widget value => path in the nutrients model

    $paths = [
      'calories' => 'calories',              // nutrients/-this.yml, belongs to no group
      'fat'      => 'lipids/fattyAcids',
      'amino'    => 'aminoAcids',
      'carbs'    => 'carbs',
      'sugar'    => 'carbs.substances.Sugar',
      'fibre'    => 'carbs.substances.Fibre',
      'salt'     => 'minerals.substances.Salt',
      'water'    => 'misc.substances.water'
    ];

    foreach( $paths as $metric => $path )
    {
      $amounts = $this->nutrientsModel->get("$path.amounts");

      if( empty($amounts))
        continue;

      // The regular day: entries with a dayType are variants of it (reduced, fillUp, workout).
      // Person type is hard coded to the first match, same as makeNutrientsView()

      $a = null;

      foreach( $amounts as $entry )
        if( ! isset( $entry['criteria']['dayType']))
        {
          $a = $entry;
          break;
        }

      if( ! $a )
        continue;

      $this->widgetRanges[$metric] = [
        'lower' => $this->calculateBound( $a['amount'], $a['lower']),
        'upper' => $this->calculateBound( $a['amount'], $a['upper'], true)
      ];
    }

    $this->widgetRanges = array_merge( $this->widgetRanges, User::current('settings.widgetRanges') ?? []);
  }


  /*@

  Bounds of one widget value as data attributes, printed on its span in the view.
  Empty if the value has no range (sugar)

  */
  public function rangeAttribs( string $metric ) : string  /*@*/
  {
    if( ! isset( $this->widgetRanges[$metric]))
      return '';

    $range = $this->widgetRanges[$metric];

    return " data-lower=\"$range[lower]\" data-upper=\"$range[upper]\"";
  }


  /*@

  Helper for makeNutrientsView(): calc acceptable nutrient intake based on ideal amount with tolerance
  
  ARGS:
    amount:  Ideal amount
    bound:   Percentage tolerance like "5%" or the bound itself like 4 (salt: 4 - 5 - 6 g)
    isUpper: Whether this is an upper or lower bound calculation

  RETURN: float calculated bound value

  */
  private function calculateBound( $amount, $bound, $isUpper = false ) : float
  {
    if( false === strpos( (string) $bound, '%'))
      return (float) $bound;  // absolute: all data files use lower < amount < upper

    $percentage = floatval($bound) / 100;  // remove percent sign and convert to decimal

    return $isUpper
      ? $amount + ($amount * $percentage)
      : $amount - ($amount * $percentage);
  }
}

?>
