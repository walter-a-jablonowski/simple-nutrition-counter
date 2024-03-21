<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/parse_tsv.php';


// Make all the data
// we currently use no Engine cause the app is tooooooooooo small

$config = new SimpleData( Yaml::parse( file_get_contents('config.yml')));
$data   = new SimpleData();

$data->foodsTxt = file_get_contents('data/foods.yml');
$foodsDef = Yaml::parse( $data->foodsTxt );

// Make food list with amounts

foreach( $foodsDef as $food => $entry )
{
  if( $entry['packaging'] === 'pack')
  {
    // $usedAmounts = $entry['usedAmounts'] ?? ['1/4' => 1/4, '1/3' => 1/3, '1/2' => 1/2, '2/3' => 2/3, '3/4' => 3/4, '1' => 1];
    $usedAmounts = $entry['usedAmounts'] ?? $config->get('foods.defaultAmounts.pack');

    foreach( $usedAmounts as $amount => $multipl )
    {
      eval("\$multipl = $multipl;");  // 1/2 => 0.5
      
      $data->push('foods', ["$food $amount" => [
        'weight'    => round( $entry['weight']   * $multipl, 1),
        'calories'  => round( $entry['calories'] * $multipl, 1),
        'nutrients' => [
          'fat'     => round( $entry['nutrients']['fat']   * $multipl, 1),
          'amino'   => round( $entry['nutrients']['amino'] * $multipl, 1),
          'salt'    => round( $entry['nutrients']['salt']  * $multipl, 1)
        ]
      ]]);
    }
  }
  elseif( $entry['packaging'] === 'pieces')
  {
    // $usedAmounts = $entry['usedAmounts'] ?? [1, 2, 3];
    $usedAmounts = $entry['usedAmounts'] ?? $config->get('foods.defaultAmounts.pieces');

    foreach( $usedAmounts as $amount )

      $data->push('foods', ["$food $amount" => [
        'weight'    => round(( $entry['weight']   / $entry['quantity'] ) * $amount, 1),
        'calories'  => round(( $entry['calories'] / $entry['quantity'] ) * $amount, 1),
        'nutrients' => [
          'fat'     => round(( $entry['nutrients']['fat']   / $entry['quantity'] ) * $amount, 1),
          'amino'   => round(( $entry['nutrients']['amino'] / $entry['quantity'] ) * $amount, 1),
          'salt'    => round(( $entry['nutrients']['salt']  / $entry['quantity'] ) * $amount, 1)
        ]
      ]]);
  }
  else  // single piece
  {
    $data->push('foods', [$food => [
      'weight'    => $entry['weight'],
      'calories'  => $entry['calories'],
      'nutrients' => [
        'fat'     => $entry['nutrients']['fat'],
        'amino'   => $entry['nutrients']['amino'],
        'salt'    => $entry['nutrients']['salt']
      ]
    ]]);
  }
}

// This day

$data->dayEntriesTxt  = trim( @file_get_contents('data/days/' . date('Y-m-d') . '.tsv') ?: '');
$data->dayEntries     = parse_tsv( $data->dayEntriesTxt);

$data->dayCaloriesSum = ! $data->dayEntries ? 0 : array_sum( array_column( $data->dayEntries, 1));
$data->dayFatSum      = ! $data->dayEntries ? 0 : array_sum( array_column( $data->dayEntries, 2));
$data->dayAminoSum    = ! $data->dayEntries ? 0 : array_sum( array_column( $data->dayEntries, 3));
$data->daySaltSum     = ! $data->dayEntries ? 0 : array_sum( array_column( $data->dayEntries, 4));

// All days

foreach( scandir('data/days', SCANDIR_SORT_DESCENDING) as $file)
{
  if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
    continue;

  $dat     = pathinfo($file, PATHINFO_FILENAME);
  $entries = parse_tsv( file_get_contents("data/days/$file"));

  $data->push('lastDaysSums', [$dat => [
    'caloriesSum' => ! $entries ? 0 : array_sum( array_column($entries, 1)),
    'fatSum'      => ! $entries ? 0 : array_sum( array_column($entries, 2)),
    'aminoSum'    => ! $entries ? 0 : array_sum( array_column($entries, 3)),
    'saltSum'     => ! $entries ? 0 : array_sum( array_column($entries, 4))
  ]]);
}

( function( $data ) {  // make a scope, making a block only isn't enough in PHP

  require 'view.php';

})( $data );

?>