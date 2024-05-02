<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

chdir('../src');

require_once 'vendor/autoload.php';


// Look for missing prices and old prices

define('OLD_PRICE', 6 * 30);


$foods = Yaml::parse( file_get_contents('data/foods.yml'));
$r = [];

foreach( $foods as $key => $food)
{
  $lastPriceUpdate = isset($food['lastPriceUpd']) && is_numeric($food['lastPriceUpd'])
                   ? (new DateTime())->setTimestamp($food['lastPriceUpd'])
                   : new DateTime();

  $diff = (new DateTime())->diff($lastPriceUpdate);

  if( ! $food['price'] || $diff->days > OLD_PRICE )
  {
    $r[$key] = [
      'price' => $food['price'],
      'lastPriceUpd' => $food['lastPriceUpd']
    ];
  }
}

// Print (AI generated modified)

$maxKeyLen   = max( array_map('strlen', array_keys($r))) + 1;
$maxPriceLen = max( array_map( fn($item) => strlen($item['price']), $r));

foreach( $r as $key => $value)
{
  print str_pad("$key:", $maxKeyLen + 1)
      . str_pad($value['price'], $maxPriceLen + 2)
      . " ({$value['lastPriceUpd']})\n";
}

?>
