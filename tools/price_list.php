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

  if( $food == 'Riegel Deca Basic')
    $debug = 'halt';

  if( is_null($data['lastPriceUpd']) || ( $min_date === null || $data['lastPriceUpd'] >= $min_date ))
    $r[ $data['vendor']][] = [
      'food'  => $food,
      'price' => $data['price'],
      'lastPriceUpd' => $data['lastPriceUpd'] ? date('Y-m-d', $data['lastPriceUpd']) : ''
    ];
}

$output = '';

foreach( $r as $vendor => $foods )  
{
  $output .= "\n$vendor\n\n";
  
  foreach( $foods as $entry )  // TASK: looks like formatting problem if Umlaut in name
    $output .= str_pad( $entry['food'], 26) . str_pad( $entry['price'], 5, ' ', STR_PAD_LEFT) . "  $entry[lastPriceUpd]\n";
}

file_put_contents( $out_file, $output );

?>
