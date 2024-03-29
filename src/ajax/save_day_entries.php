<?php

trait SaveDayEntriesAjaxController
{
  public function saveDayEntries( $request ) {

    // we use no backup here, just start from scratch if error

    // $data = json_decode( file_get_contents('php://input'), true);

    if( ! file_put_contents('data/days/' . date('Y-m-d') . '.tsv', $request['data']))
      echo json_encode(['result' => 'error', 'message' => 'Error saving']);

    echo json_encode(['result' => 'success']);
  }
}

?>
