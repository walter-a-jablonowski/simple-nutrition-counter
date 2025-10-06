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
