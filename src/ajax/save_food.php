<?php

require_once 'lib/food_import/FoodYamlWriter.php';
require_once 'models/functions.php';

/*

Ajax: create a new food record from the import form.

Writes a food yml file (name = record name = file name) and adds the food to the
layout grid. Fails if a food with that name already exists.

*/
trait SaveFoodAjaxController
{

  public function saveFood( $request )
  {
    $userId = User::current('id');
    $food   = $request['food'] ?? [];
    $name   = trim( $food['name'] ?? '');

    if( $name === '')
      return ['result' => 'error', 'data' => ['message' => 'Food name is required.']];

    if( preg_match('#[\\\\/:*?"<>|]#', $name))
      return ['result' => 'error', 'data' => ['message' => 'Food name contains invalid characters.']];

    if( find_food_source( $name, $userId))
      return ['result' => 'error', 'data' => ['message' => "A food named \"$name\" already exists."]];

    // Ensure required meta is present even for manual (non-imported) saves

    $food['sources'] = $food['sources'] ?? ['nutriVal' => 'web'];
    $food['lastUpd'] = $food['lastUpd'] ?? date('Y-m-d');

    if( ! empty($food['dealPrice']) && empty($food['lastDealPriceUpd']))
      $food['lastDealPriceUpd'] = date('Y-m-d');

    $filePath = "data/bundles/Default_$userId/foods/$name.yml";
    $yaml     = FoodYamlWriter::toYaml( $food );

    if( file_put_contents( $filePath, $yaml) === false)
      return ['result' => 'error', 'data' => ['message' => 'Could not write the food file.']];

    add_food_to_layout( $name, $userId );

    return ['result' => 'success', 'data' => ['name' => $name]];
  }
}

?>
