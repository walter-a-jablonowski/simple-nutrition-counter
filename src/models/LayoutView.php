<?php

trait LayoutView
{
  protected SimpleData $layoutView;


  /*@
  
  makeLayoutView()
  
  - pre calc all food and recipes amounts for food grid
  - easy print in food grid, less js logic
  
  */
  private function makeLayoutView()  /*@*/
  {
    $settings = settings::instance();
    $user     = User::current();

    $this->layoutView = new SimpleData();
    
    // Calc amount data for combined food and supplement items
    
    foreach( $this->combinedModel->all() as $name => $data )
    {
      $data['weight'] = trim($data['weight'], "mgl ");  // just for convenience, we don't need the unit here
      $usage          = $this->determineFoodUsageType($data);
      $usedAmounts    = $data['usedAmounts'] ?? ($settings->get("foods.defaultAmounts.$usage") ?: 1);

      // Check if nutrients are defined per piece instead of per 100g/ml
      $nutrientsPerPiece = isset($data['amountPer']) && $data['amountPer'] === 'piece';

      foreach( $usedAmounts as $amount )
      {
        $multipl = trim($amount, "mglpc ");
        $multipl = (float) eval("return $multipl;");  // 1/2 => 0.5 or: eval("\$multipl = $multipl;")
        
        $weight  = $this->calculateWeight($usage, $data, $multipl);
        
        $perWeight = [
          'category' => $data['category'],  // add category for distinguishing foods vs supplements
          'weight'   => round( $weight, 1),
          'calories' => $nutrientsPerPiece 
                     ?  round( $data['calories'] * $multipl, 1)          // for per-piece: multiply by pieces count
                     :  round( $data['calories'] * ($weight / 100), 1),  // for per-100g: scale by weight
          'price'    => $this->calculatePrice($data, $weight),
          'xTimeLog' => isset($data['xTimeLog']) && $data['xTimeLog'] ? true : false
        ];

        // Calculate nutritional values for all nutrient groups
        
        foreach( array_merge(['nutritionalValues'], self::NUTRIENT_GROUPS) as $groupName )
        {
          $shortName = $groupName === 'nutritionalValues' ? 'nutriVal'
                     : $this->nutrientsModel->get("$groupName.short");

          $perWeight[$shortName] = [];

          if( isset($data[$groupName]) && count($data[$groupName]) > 0)
            foreach( $data[$groupName] as $nutrient => $value )
            {
              // Skip if nutrient doesn't exist in the model (except for nutritionalValues)
              if( $groupName != 'nutritionalValues' && 
                  ! $this->nutrientsModel->has("$groupName.substances.$nutrient")) {
                continue;
              }

              $short = $groupName === 'nutritionalValues' ? $nutrient  // short name for single nutrient
                     : $this->nutrientsModel->get("$groupName.substances.$nutrient.short");

              $perWeight[$shortName][$short] = $nutrientsPerPiece
                                             ? round( $value * $multipl, 1)          // for per-piece: use the value directly multiplied by pieces count
                                             : round( $value * ($weight / 100), 1);  // for per-100g: scale by weight
            }
        }

        $safeAmount = str_replace('.', '_', $amount);  // enable floating point number as key
        $this->layoutView->set("$name.$safeAmount", $perWeight);
        // $id = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $name));  // TASK: shorten
      }
    }
  }

  /*@
  
  Helper for makeLayoutView(): determine food usage type based on data
  
  ARGS:
    data: Food data array
  
  RETURN: string usage type ('precise', 'pieces', or 'pack')
  
  */
  private function determineFoodUsageType( $data ) : string
  {
    if( isset($data['usedAmounts']) && (
        strpos($data['usedAmounts'][0], 'g')  !== false ||
        strpos($data['usedAmounts'][0], 'ml') !== false
    ))
      return 'precise';
    elseif( isset($data['pieces']) )
      return 'pieces';
    else
      return 'pack';
  }
  
  /*@
  
  Helper for makeLayoutView(): calculate weight based on usage type
  
  ARGS:
    usage:   Usage type ('precise', 'pieces', or 'pack')
    data:    Food data array
    multipl: Multiplier value
  
  RETURN: float calculated weight
  
  */
  private function calculateWeight( $usage, $data, $multipl ) : float
  {
    switch( $usage ) {
      case 'pack':
        return $data['weight'] * $multipl;
      case 'pieces':
        return ($data['weight'] / $data['pieces']) * $multipl;
      default:  // precise
        return $multipl;
    }
  }
  
  /*@
  
  Helper for makeLayoutView(): calculate price based on weight
  
  ARGS:
    data:   Food data array
    weight: Calculated weight
  
  RETURN: float calculated price
  
  */
  private function calculatePrice( $data, $weight ) : float
  {
    if( isset($data['price']) && $data['price'] )
      return round( $data['price'] * ($weight / $data['weight']), 2);
    elseif( isset($data['dealPrice']) && $data['dealPrice'] )
      return round( $data['dealPrice'] * ($weight / $data['weight']), 2);
    else
      return 0;
  }
}

?>
