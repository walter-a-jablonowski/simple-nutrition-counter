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

    $this->dayEntriesTxt = trim( @file_get_contents('data/users/' . $config->get('user') . "/days/{$this->date}.tsv") ?: '', "\n");
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

      foreach( $usedAmounts as $amount )
      {
        // if( $food == 'Milch H' )  // DEBUG
        //   $debug = 'halt';

        $multipl = trim( $amount, "mglpc ");
        $multipl = (float) eval("return $multipl;");  // 1/2 => 0.5
        // eval("\$multipl = $multipl;");

        $weight = $usage === 'pack'   ? $entry['weight'] * $multipl : (
                  $usage === 'pieces' ? ($entry['weight'] / $entry['pieces']) * $multipl
                : $multipl  // precise
        );

        $perWeight = [
          'weight'   => round( $weight, 1),
          'calories' => round( $entry['calories'] * ($weight / 100), 1),
          'price'    => isset($entry['price']) ? round( $entry['price'] * ($weight / $entry['weight']), 2) : 0
        ];

        foreach(['nutritionalValues', 'fattyAcids', 'aminoAcids', 'vitamins', 'minerals'] as $group)
        {
          if( ! isset($entry[$group]) || count($entry[$group]) == 0)
            $perWeight[$group] = [];
          else
            foreach( $entry[$group] as $name => $value)
              $perWeight[$group][$name] = round( $entry[$group][$name] * ($weight / 100), 1);
        }

        $title = str_pad( $amount, 5, ' ', STR_PAD_LEFT) . " $food";  // TASK: improve

        $this->model->set("foods.$title", $perWeight);
      }
    }

    // All days
    // no model data, kind of report

    foreach( scandir('data/users/' . $config->get('user') . '/days', SCANDIR_SORT_DESCENDING) as $file)
    {
      if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
        continue;

      $dat     = pathinfo($file, PATHINFO_FILENAME);
      $entries = parse_tsv( file_get_contents('data/users/' . $config->get('user') . "/days/$file"));

      $this->lastDaysSums[$dat] = [
        'caloriesSum' => ! $entries ? 0 : array_sum( array_column($entries, 1)),
        // 'carbsSum' => ! $entries ? 0 : array_sum( array_column($entries, 2)),
        'fatSum'      => ! $entries ? 0 : array_sum( array_column($entries, 2)),
        'aminoSum'    => ! $entries ? 0 : array_sum( array_column($entries, 3)),
        'saltSum'     => ! $entries ? 0 : array_sum( array_column($entries, 4)),
        'priceSum'    => ! $entries ? 0 : array_sum( array_column($entries, 4))
      ];
    }
  }


  public function render(/*$request*/)
  {
    $this->date = $_GET['date'] ?? date('Y-m-d');
    $this->mode = isset($_GET['date']) ? 'last' : 'current';
    
    $this->makeData();

    ob_start();
    require 'view/-this.php';
    return ob_get_clean();    // echo is done in index
  }
}

?>
