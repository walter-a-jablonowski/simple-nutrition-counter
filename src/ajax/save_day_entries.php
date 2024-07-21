<?php

trait SaveDayEntriesAjaxController
{

  public function saveDayEntries( $request )
  {
    $config = config::instance();

    // we use no backup here, just start from scratch if error

    if( ! file_put_contents('data/users/' . $config->get('defaultUser') . "/days/$request[date].tsv", $request['data']))
      return ['result' => 'error', 'message' => 'Error saving'];

    return ['result' => 'success'];
  }
}

?>
