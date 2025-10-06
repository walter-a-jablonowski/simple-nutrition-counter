<?php

require_once 'lib/yml_preserve_MOV/yml_preserve.php';
require_once 'models/functions.php';

trait SavePriceAjaxController
{

  public function savePrice( $request )
  {
    $userId    = User::current('id');
    $foodName  = $request['name'];
    $priceType = $request['priceType'] ?? 'price'; // 'price' or 'dealPrice'
    $newValue  = $request['value'];

    // Check if this is a variant
    $sourceInfo = find_food_source( $foodName, $userId );
    // error_log( print_r($sourceInfo, true) );
    if( $sourceInfo && $sourceInfo['isVariant'])
      return ['result' => 'error', 'message' => 'Updating variant prices is unsupported yet'];

    // Determine which field and history key to update
    $fieldName = $priceType;
    $dateField = $priceType === 'price' ? 'lastPriceUpd' : 'lastDealPriceUpd';
    $historyKey = $priceType === 'price' ? 'prices' : 'dealPrices';

    // Update the price with history
    if( ! update_price_with_history( $foodName, $fieldName, $dateField, $historyKey, $newValue, $userId ))
      return ['result' => 'error', 'message' => 'Error saving price - food missing or file error'];
      
    return ['result' => 'success'];
  }
}

?>
