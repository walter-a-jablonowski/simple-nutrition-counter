<?php

trait ChangeUserAjaxController
{

  public function saveFoods( $request )
  {
    $_SESSION['userId'] = $request['userId'];

    // return ['result' => 'error', 'message' => 'Error saving'];
    return ['result' => 'success'];
  }
}

?>
