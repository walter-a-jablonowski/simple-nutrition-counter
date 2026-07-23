<?php

require_once 'models/functions.php';

/*

Ajax: move an existing food to another group in the layout grid.

Input:  { food, tab, group, after } - the food name, the target tab + group
        display name and the position: '' = top, otherwise the entry to insert
        behind (see layout_insert_food).
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
    $after  = trim( $request['after'] ?? '');

    if( $food === '' || $tab === '' || $group === '')
      return ['result' => 'error', 'data' => ['message' => 'Missing move target.']];

    if( ! move_food_in_layout( $food, $userId, $tab, $group, $after))
      return ['result' => 'error', 'data' => ['message' => 'Could not move the food (target group not found).']];

    return ['result' => 'success', 'data' => []];
  }
}

?>
