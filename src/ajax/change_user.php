<?php

trait ChangeUserAjaxController
{

  public function saveFoods( $request )
  {
    $_SESSION['user'] = $request['user'];

    // return ['result' => 'error', 'message' => 'Error saving'];
    return ['result' => 'success'];
  }
}

?>
