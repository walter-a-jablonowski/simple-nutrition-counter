<?php

trait SaveFoodsAjaxController
{
  public function saveFoods( $request ) {

    // require_once 'lib/backup_230924.php';

    $data = json_decode( file_get_contents('php://input'), true);

    // if( ! backup_fil( $groupsFil ))  // prefer cause we have on backup form for all dools
    //   echo json_encode(['result' => 'error', 'message' => 'Error making backup']);  // TASK: add in js

    if( ! file_put_contents('data/foods.yml', $data['data']))
      echo json_encode(['result' => 'error', 'message' => 'Error saving']);

    echo json_encode(['result' => 'success']);
  }
}

?>
