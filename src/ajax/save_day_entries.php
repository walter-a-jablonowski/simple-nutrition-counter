<?php

trait SaveDayEntriesAjaxController
{

  public function saveDayEntries( $request )
  {
    // we use no backup here, just start from scratch if error

    if( ! file_put_contents('data/days/' . date('Y-m-d') . '.tsv', $request['data']))
      return ['result' => 'error', 'message' => 'Error saving'];

    return ['result' => 'success'];
  }
}

?>
