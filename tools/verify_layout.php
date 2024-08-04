<?php

chdir('../src');

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';


# TASK: layout was modified (added list key)
# this maybe stll can be used cause can show more than Misc foods group?

// TASK: AI suggests "layout.yml might contain duplicate entries under different categories"

$foods  = array_keys( Yaml::parse( file_get_contents('data/bundles/Default_JaneDoe@example.com-24080101000000/foods.yml')));
$layout = Yaml::parse( file_get_contents('data/bundles/Default_JaneDoe@example.com-24080101000000/layouts/food.yml'));

$layoutFoods = [];

foreach($layout as $category => $items)
{
  if( ! $items )  continue;          // no entries

  foreach( $items as $item)
  {
    if( is_array($item))  continue;  // (i)
    $layoutFoods[] = $item;
  }
}

$missingInLayout = array_diff( $foods, $layoutFoods);
$missingInFoods  = array_diff( $layoutFoods, $foods);

// AI generated

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Food List Comparison</title>
<style>
  body { font-family: Arial, sans-serif; }
  h2 { color: #333; }
  ul { list-style-type: none; padding: 0; }
  li { margin: 5px 0; padding: 5px; background-color: #f9f9f9; border: 1px solid #ddd; }
</style>
</head>
<body>
<h2>Foods missing in layout</h2>
<ul>
  <?php foreach( $missingInLayout as $food): ?>
    <li><?php echo htmlspecialchars($food); ?></li>
  <?php endforeach; ?>
</ul>

<h2>Foods missing in foods list</h2>
<ul>
  <?php foreach( $missingInFoods as $food): ?>
    <li><?php echo htmlspecialchars($food); ?></li>
  <?php endforeach; ?>
</ul>
</body>
</html>