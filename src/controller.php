<?php

use Symfony\Component\Yaml\Yaml;
// use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/parse.php';


// Make all the data

$data['foodsTxt'] = file_get_contents('data/foods.yml');
$foodsDef = Yaml::parse( $data['foodsTxt'] );

// Make food list with amounts

$data['foods'] = [];

foreach( $foodsDef as $food => $entry )
{
  if( $entry['packaging'] === 'pack')
  {
    $usedAmounts = $entry['usedAmounts'] ?? ['1/4' => 1/4, '1/3' => 1/3, '1/2' => 1/2, '2/3' => 2/3, '3/4' => 3/4, '1' => 1];

    foreach( $usedAmounts as $frac => $multipl )
    {
      if( is_string($multipl))  // calc if loaded from yml cause string
        eval("\$multipl = $data[$frac];");

      $data['foods']["$food $frac"] = [  // TASK: (adv) maybe add a nutrients key as in data source
        'weight'   => round( $entry['weight']   * $multipl, 1),
        'calories' => round( $entry['calories'] * $multipl, 1),
        'amino'    => round( $entry['nutrients']['amino']    * $multipl, 1),
        'salt'     => round( $entry['nutrients']['salt']     * $multipl, 1)
      ];
    }
  }
  elseif( $entry['packaging'] === 'pieces')
  {
    $usedAmounts = $entry['usedAmounts'] ?? [1, 2, 3];

    foreach( $usedAmounts as $amount )
      $data['foods']["$food $amount"] = [
        'weight'   => round(( $entry['weight']   / $entry['quantity'] ) * $amount, 1),
        'calories' => round(( $entry['calories'] / $entry['quantity'] ) * $amount, 1),
        'amino'    => round(( $entry['nutrients']['amino']    / $entry['quantity'] ) * $amount, 1),
        'salt'     => round(( $entry['nutrients']['salt']     / $entry['quantity'] ) * $amount, 1)
      ];
  }
  else  // single piece
  {
    $data['foods'][$food] = [
      'weight'   => $entry['weight'],
      'calories' => $entry['calories'],
      'amino'    => $entry['nutrients']['amino'],
      'salt'     => $entry['nutrients']['salt']
    ];
  }
}

// This day

$data['dayEntriesTxt']  = trim( @file_get_contents('data/days/' . date('Y-m-d') . '.tsv') ?: '');
$data['dayEntries']     = parse($data['dayEntriesTxt']);

$data['dayCaloriesSum'] = ! $data['dayEntries'] ? 0 : array_sum( array_column( $data['dayEntries'], 1));
$data['dayAminoSum']    = ! $data['dayEntries'] ? 0 : array_sum( array_column( $data['dayEntries'], 2));
$data['daySaltSum']     = ! $data['dayEntries'] ? 0 : array_sum( array_column( $data['dayEntries'], 3));

// All days

$data['lastDaysSums'] = [];

foreach( scandir('data/days', SCANDIR_SORT_DESCENDING) as $file)
{
  if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
    continue;

  $dat     = pathinfo($file, PATHINFO_FILENAME);
  $entries = parse( file_get_contents("data/days/$file"));

  $data['lastDaysSums'][$dat] = [
    'caloriesSum' => ! $entries ? 0 : array_sum( array_column($entries, 1)),
    'aminoSum'    => ! $entries ? 0 : array_sum( array_column($entries, 2)),
    'saltSum'     => ! $entries ? 0 : array_sum( array_column($entries, 3))
  ];
}

( function( $data ) {  // make a scope, making a block only isn't enough in PHP

  extract($data);

  require 'view.php';

})( $data );

?>
