<?php

require_once 'lib/yml_preserve_MOV/yml_preserve.php';

trait SavePriceAjaxController
{

  public function savePrice( $request )
  {
    // TASK: add user
    // TASK: maybe add stuff from misc ajax

    $file = "data/bundles/Default_$user->id/foods/$request[name].yml";
    $data = yml_replace_value( file_get_contents($file), 'price', $request['price']);

    if( ! file_put_contents( $file, $data))
      return ['result' => 'error', 'message' => 'Error saving'];
      
    echo json_encode(['status' => 'success']);
  }
}

?>
