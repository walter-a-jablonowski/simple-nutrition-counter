<?php

require_once 'lib/yml_preserve_MOV/yml_preserve.php';
require_once 'models/functions.php';

trait SavePriceAjaxController
{

  public function savePrice( $request )
  {
    // TASK: add user
    // TASK: maybe add stuff from misc ajax

    $userId = User::current('id');
    $foodName = $request['name'];
    $newPrice = $request['price'];

    // Use the new update_food_price function that handles regular foods and variants
    if( ! update_food_price( $foodName, $newPrice, $userId ))
      return ['result' => 'error', 'message' => 'Error saving price - food not found or file error'];

    // TASK: save prev price
      
    echo json_encode(['status' => 'success']);
  }
}

?>
