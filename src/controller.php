<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/Controller_240323/ControllerBase.php';
require_once 'ajax/save_day_entries.php';
require_once 'ajax/save_foods.php';
require_once 'lib/parse_tsv.php';


class FoodsController extends ControllerBase
{
  use SaveDayEntriesAjaxController;
  use SaveFoodsAjaxController;


  public function __construct(/*$model = null, $view = null*/)
  {
    parent::__construct();
  }


  public function render(/*$request*/) {

    $config = config::instance();

    // Make all the data

    $data = new SimpleData();

    $this->debug = $config->get('debug');
    
    $data->foodsTxt = file_get_contents('data/foods.yml');
    $foodsDef = Yaml::parse( $data->foodsTxt );

    // Make food list with amounts

    // we pre calc all values cause it's simpler for recipes

    foreach( $foodsDef as $food => $entry )
    {
      // TASK: simplify, rm duplicate code
/*
      $usedAmounts = $entry['usedAmounts'] ?? ( $config->get("foods.defaultAmounts.$entry[packaging]") ?: 1);
      // $usedAmounts = $entry['usedAmounts'] ?? ( config::get("foods.defaultAmounts.$entry[packaging]") ?: 1);

      foreach( $usedAmounts as $amount )  // TASK: modify in config (no key)
      {
        $multipl = $amount;
        
        if( $entry['packaging'] == 'pack')
          eval("\$multipl = $multipl;");  // 1/2 => 0.5

        $weight = [
          'pack'   => $entry['weight'] * $multipl
          'pieces' => $weight = ($entry['weight'] / $entry['quantity']) * $multipl
          'piece'  => $entry['weight']
        ][ $entry['packaging']];

        $data->push('foods', ["$food $amount" => [
          'weight'    => round( $weight, 1),
          'calories'  => round( $entry['calories'] * ($weight / 100), 1),
          'nutrients' => [
            'fat'     => round( $entry['nutrients']['fat']   * ($weight / 100), 1),
            'amino'   => round( $entry['nutrients']['amino'] * ($weight / 100), 1),
            'salt'    => round( $entry['nutrients']['salt']  * ($weight / 100), 1)
          ]
        ]]);
      }        
*/
      if( $entry['packaging'] === 'pack')
      {
        $usedAmounts = $entry['usedAmounts'] ?? $config->get('foods.defaultAmounts.pack');
        // $usedAmounts = $entry['usedAmounts'] ?? config::get('foods.defaultAmounts.pack');

        foreach( $usedAmounts as $amount => $multipl )
        {
          eval("\$multipl = $multipl;");  // 1/2 => 0.5

          $weight = $entry['weight'] * $multipl;
          
          $data->push('foods', ["$food $amount" => [
            'weight'    => round( $weight, 1),
            'calories'  => round( $entry['calories'] * ($weight / 100), 1),
            'nutrients' => [
              'fat'     => round( $entry['nutrients']['fat']   * ($weight / 100), 1),
              'amino'   => round( $entry['nutrients']['amino'] * ($weight / 100), 1),
              'salt'    => round( $entry['nutrients']['salt']  * ($weight / 100), 1)
            ]
          ]]);
        }
      }
      elseif( $entry['packaging'] === 'pieces')
      {
        $usedAmounts = $entry['usedAmounts'] ?? $config->get('foods.defaultAmounts.pieces');
        // $usedAmounts = $entry['usedAmounts'] ?? config::get('foods.defaultAmounts.pieces');
          
        foreach( $usedAmounts as $amount )
        {
          $weight = ($entry['weight'] / $entry['quantity']) * $amount;

          $data->push('foods', ["$food $amount" => [
            'weight'    => round( $weight, 1),
            'calories'  => round( $entry['calories'] * ($weight / 100), 1),
            'nutrients' => [
              'fat'     => round( $entry['nutrients']['fat']   * ($weight / 100), 1),
              'amino'   => round( $entry['nutrients']['amino'] * ($weight / 100), 1),
              'salt'    => round( $entry['nutrients']['salt']  * ($weight / 100), 1)
            ]
          ]]);
        }
      }
      else  // single piece
      {
        $weight = $entry['weight'];
        
        $data->push('foods', [$food => [
          'weight'    => $weight,
          'calories'  => round( $entry['calories'] * ($weight / 100), 1),
          'nutrients' => [
            'fat'     => round( $entry['nutrients']['fat']   * ($weight / 100), 1),
            'amino'   => round( $entry['nutrients']['amino'] * ($weight / 100), 1),
            'salt'    => round( $entry['nutrients']['salt']  * ($weight / 100), 1)
          ]
        ]]);
      }
    }

    // This day

    $data->dayEntriesTxt = trim( @file_get_contents('data/days/' . date('Y-m-d') . '.tsv') ?: '');
    $data->dayEntries    = parse_tsv( $data->dayEntriesTxt);

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

    $this->model = $data;


    // we currently use no Engine cause the app is tooooooooooo small

    // ( function( $data ) {  // make a scope, making a block only isn't enough in PHP
    //
    //   require 'view.php';
    //
    // })( $this->data );

    ob_start();
    require 'view.php';
    return ob_get_clean();
  }
}

?>
