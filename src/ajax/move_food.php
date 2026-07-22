<?php

require_once 'models/functions.php';

/*

Ajax: move an existing food to another group in the layout grid.

Input:  { food, tab, group } - the food name and the target tab + group display name.
Output: { } on success; the grid is reloaded client-side.

*/
trait MoveFoodAjaxController
{

  public function moveFood( $request )
  {
    $userId = User::current('id');
    $food   = trim( $request['food']  ?? '');
    $tab    = trim( $request['tab']   ?? '');
    $group  = trim( $request['group'] ?? '');

    if( $food === '' || $tab === '' || $group === '')
      return ['result' => 'error', 'data' => ['message' => 'Missing move target.']];

    if( ! move_food_in_layout( $food, $userId, $tab, $group))
      return ['result' => 'error', 'data' => ['message' => 'Could not move the food (target group not found).']];

    return ['result' => 'success', 'data' => []];
  }
}

?>
