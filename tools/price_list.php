<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once '../src/vendor/autoload.php';

$min_date = null;
// $min_date = '2024-05-16';  // very old prioes only
$out_file = 'price_list.tsv';

$foods = Yaml::parseFile('../src/data/foods.yml');
$r = [];

foreach( $foods as $food => $data )
{
  // Symfony yml parses dates as timestamps

  // $last_price_upd = ! $data['lastPriceUpd'] ? null : DateTime::createFromFormat('Y-m-d', $data['lastPriceUpd']);
  // $minDateObj     = ! $min_date ? null : DateTime::createFromFormat('Y-m-d', $min_date);
  
  if( is_null($data['lastPriceUpd']) || ( $min_date === null || $data['lastPriceUpd'] >= $min_date ))
    $r[ $data['vendor']][] = [
      'food'  => $food,
      'price' => $data['price'],
      'lastPriceUpd' => $data['lastPriceUpd'] ? date('Y-m-d', $data['lastPriceUpd']) : ''
    ];
}

$output = '';

foreach( $r as $vendor => $foods )  // TASK: missing prices still displayed as 0.00
{
  $output .= "\n$vendor\n\n";
  
  foreach( $foods as $entry )
    $output .= sprintf("%-23s  %-6.2f  %-10s\n", $entry['food'], $entry['price'] ?: '', $entry['lastPriceUpd']);
}

file_put_contents( $out_file, $output );

?>
