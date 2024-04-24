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
    $this->debug = $config->get('debug');

    // This day and foods tab

    $this->dayEntriesTxt = trim( @file_get_contents('data/days/' . date('Y-m-d') . '.tsv') ?: '', "\n");
    $this->dayEntries    = parse_tsv( $this->dayEntriesTxt );

    $this->foodsTxt = file_get_contents('data/foods.yml');
    $foodsDef = Yaml::parse( $this->foodsTxt );

    // make food list with amounts (model)

    $this->model = new SimpleData();

    foreach( $foodsDef as $food => $entry )
    {
      $entry['weight'] = trim( $entry['weight'], "mgl ");  // just for convenience, we don't need the unit here

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
        $multipl = trim( $amount, "mglpc ");
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

        $this->model->push("foods.$title", [  // TASK: use set push for index only
          'weight'    => round( $weight, 1),
          'calories'  => round( $entry['calories'] * ($weight / 100), 1),
          'nutrients' => [
            'fat'     => round( $entry['nutrients']['fat']   * ($weight / 100), 1),
            'amino'   => round( $entry['nutrients']['amino'] * ($weight / 100), 1),
            'salt'    => round( $entry['nutrients']['salt']  * ($weight / 100), 1)
          ]
        ]);
      }
    }

    // All days
    // no model data, kind of report

    foreach( scandir('data/days', SCANDIR_SORT_DESCENDING) as $file)
    {
      if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
        continue;

      $dat     = pathinfo($file, PATHINFO_FILENAME);
      $entries = parse_tsv( file_get_contents("data/days/$file"));

      $this->lastDaysSums[$dat] = [
        'caloriesSum' => ! $entries ? 0 : array_sum( array_column($entries, 1)),
        'fatSum'      => ! $entries ? 0 : array_sum( array_column($entries, 2)),
        'aminoSum'    => ! $entries ? 0 : array_sum( array_column($entries, 3)),
        'saltSum'     => ! $entries ? 0 : array_sum( array_column($entries, 4))
      ];
    }
  }


  public function render(/*$request*/)
  {
    $this->makeData();

    ob_start();
    require 'view/-this.php';
    return ob_get_clean();    // echo is done in index
  }
}

?>
