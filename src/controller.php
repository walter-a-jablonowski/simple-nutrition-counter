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


  /*@

  Make all the data
  we pre calc all values cause it's simpler for recipes

  */
  private function makeData()  /*@*/
  {
    $config = config::instance();
    $data   = new SimpleData();

    $this->debug = $config->get('debug');
    
    $data->foodsTxt = file_get_contents('data/foods.yml');
    $foodsDef = Yaml::parse( $data->foodsTxt );

    // Make food list with amounts

    foreach( $foodsDef as $food => $entry )
    {
      $usage = isset( $entry['usedAmounts']) && ( strpos( $entry['usedAmounts'][0], 'g') !== false || strpos( $entry['usedAmounts'][0], 'ml') !== false)
             ? 'precise' : (
               isset($entry['pieces'])
             ? 'pieces'
             : 'pack'
      );

      $usedAmounts = $entry['usedAmounts'] ?? ( $config->get("foods.defaultAmounts.$usage") ?: 1);
      // $usedAmounts = $entry['usedAmounts'] ?? ( config::get("foods.defaultAmounts.$usage") ?: 1);

      // print $food;  // DEBUG

      foreach( $usedAmounts as $amount )
      {
        $multipl = trim( $amount, "mgl ");
        $multipl = (float) eval("return $multipl;");            // 1/2 => 0.5
        // eval("\$multipl = $multipl;");

        // $weight = ([                                         // trick use array, more readable but unusual
        //   'pack'    => fn() => $entry['weight'] * $multipl,  // would be evaluated first => use function
        //   'pieces'  => fn() => ($entry['weight'] / $entry['pieces']) * $multipl,
        //   'precise' => fn() => $multipl
        // ][ $usage ])();

        // if( $food == 'Amino NaDuRia Pur' )  // DEBUG
        //   $debug = 'halt';

        $weight = $usage === 'pack'   ? $entry['weight'] * $multipl : (
                  $usage === 'pieces' ? ($entry['weight'] / $entry['pieces']) * $multipl
                : $multipl  // precise
        );

        $title = str_pad( $amount, 3, ' ', STR_PAD_LEFT) . " $food";

        $data->push('foods', [ $title => [
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
  }


  public function render(/*$request*/)
  {
    $this->makeData();

    // we currently use no Engine cause the app is tooooooooooo small

    ob_start();               // (TASK) for impoving this available
    require 'view/tabs.php';
    $tabs = ob_get_clean();

    ob_start();
    require 'view/tab_content/edit.php';
    $tabs = str_replace('{edit_tab}', ob_get_clean(), $tabs);

    ob_start();
    require 'view/tab_content/nutrients.php';
    $tabs = str_replace('{nutrients_tab}', ob_get_clean(), $tabs);

    ob_start();
    require 'view/tab_content/last_days.php';
    $tabs = str_replace('{days_tab}', ob_get_clean(), $tabs);

    ob_start();
    require 'view/tab_content/foods.php';
    $tabs = str_replace('{foods_tab}', ob_get_clean(), $tabs);

    ob_start();
    require 'view/index.php';
    $index = ob_get_clean();
    
    $index = str_replace('{content}', $tabs, $index);

    echo $index;
  }
}

?>
