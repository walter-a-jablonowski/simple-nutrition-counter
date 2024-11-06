<?php

trait SaveDayEntriesAjaxController
{

  public function saveDayEntries( $request )
  {
    $config = config::instance();

    // we use no backup here, just start from scratch if error

    // TASK: (advanced) add time on server and response (currently a problem cause we still use save btn))

    // $time  = date('His');
    // $data = "$time  $request[data]";  // TASK: update default user v use current
    
    if( ! file_put_contents('data/users/' . $config->get('defaultUser') . "/days/$request[date].tsv", $request['data']))
    // if( ! file_put_contents('data/users/' . $config->get('defaultUser') . "/days/$request[date].tsv", $data))
      return ['result' => 'error', 'message' => 'Error saving'];

    return ['result' => 'success'];
    // return ['result' => 'success', 'time' => $time];
  }
}

?>
