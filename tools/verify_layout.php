<?php

chdir('../src');

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';


// TASK: AI suggests "layout.yml might contain duplicate entries under different categories"

$foods  = array_keys( Yaml::parse( file_get_contents('data/foods.yml')));
$layout = Yaml::parse( file_get_contents('data/layout.yml'));

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

echo "Foods missing in layout:\n";
print_r($missingInLayout);

echo "Foods missing in foods list:\n";
print_r($missingInFoods);

?>
