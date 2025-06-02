<?php

trait NutrientsView
{
  protected SimpleData $nutrientsView;


  /*@
  
  makeNutrientsView()
  
  - pre calc nutrients (tab 2) recommended amounts per day
  - easy print in html, less js logic
  
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

  Helper for makeNutrientsView(): calc acceptable nutrient intake based on ideal amount with tolerance
  
  ARGS:
    amount:  Ideal amount
    bound:   Tolerance value (absolute or percentage)
    isUpper: Whether this is an upper or lower bound calculation

  RETURN: float calculated bound value

  */
  private function calculateBound( $amount, $bound, $isUpper = false ) : float
  {
    $isPercentage = strpos($bound, '%') !== false;
    
    if( $isPercentage ) {
      $percentage = floatval($bound) / 100;  // remove percent sign and convert to decimal
      return $isUpper 
        ? $amount + ($amount * $percentage)
        : $amount - ($amount * $percentage);
    }
    else {
      return $isUpper 
        ? $amount + $bound
        : $amount - $bound;
    }
  }
}

?>
