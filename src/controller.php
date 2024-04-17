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
      // TASK: only for compatibility, do we need packaging at all?

      $packaging = isset($entry['perPiece']) && $entry['weight'] == $entry['perPiece']
                 ? 'piece' : (
                   isset($entry['perPiece'])
                 ? 'pieces'
                 : 'pack');

      // TASK: simplify, rm duplicate code
/*
      // TASK: fix return by value only in get()

      $usedAmounts = $entry['usedAmounts'] ?? ( $config->get("foods.defaultAmounts.$packaging") ?: 1);
      // $usedAmounts = $entry['usedAmounts'] ?? ( config::get("foods.defaultAmounts.$packaging") ?: 1);
      
      foreach( $usedAmounts as $amount )  // TASK: modify in config (no key)
      {
        $multipl = $amount;
        
        if( strpos( $amount, 'g') !== false || strpos( $amount, 'ml') !== false )  // works for ml if weight also is ml
          $weight = (int) trim( $amount, "gml ");
        else
        {

          if( $packaging == 'pack')
            eval("\$multipl = $multipl;");  // 1/2 => 0.5
            // $multipl = eval("return $multipl;");

            $weight = $packaging == 'pack'
                    ? $entry['weight'] * $multipl : (
                      $packaging == 'pieces'
                    ? $entry['perPiece'] * $multipl
                    : $entry['weight']  // piece
            );
          }
        }

        $data->push('foods', ["$food $amount" => [
          'weight'    => round( $weight, 1),
          'calories'  => round( $entry['calories'] * ($weight / 100), 1),
          'nutrients' => [
            'fat'     => round( $entry['nutrients']['fat']   * ($weight / 100), 1),
            'amino'   => round( $entry['nutrients']['amino'] * ($weight / 100), 1),
            'salt'    => round( $entry['nutrients']['salt']  * ($weight / 100), 1)
          ]
        ]]);
*/
// /*
      if( $packaging === 'pack')
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
      elseif( $packaging === 'pieces')
      {
        $usedAmounts = $entry['usedAmounts'] ?? $config->get('foods.defaultAmounts.pieces');
        // $usedAmounts = $entry['usedAmounts'] ?? config::get('foods.defaultAmounts.pieces');
          
        foreach( $usedAmounts as $amount )
        {
          // $weight = ($entry['weight'] / $entry['quantity']) * $amount;
          $weight = $entry['perPiece'] * $amount;

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
// */
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
