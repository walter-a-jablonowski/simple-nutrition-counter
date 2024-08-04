<?php

// require_once 'lib/backup_230924.php';

// yml edit unused

trait SaveFoodsAjaxController
{

  public function saveFoods( $request )
  {
    // if( ! backup_fil( $groupsFil ))  // prefer cause we have on backup form for all dools
    //   return ['result' => 'error', 'message' => 'Error making backup'];

    if( ! file_put_contents('data/bundles/Default_JaneDoe@example.com_24080101000000/foods.yml', $request['data']))
      return ['result' => 'error', 'message' => 'Error saving'];

    return ['result' => 'success'];
  }
}

?>
