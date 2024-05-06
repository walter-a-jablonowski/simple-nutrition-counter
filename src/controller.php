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

        $weight = $usage === 'pack'   ? $entry['weight'] * $multipl : (
                  $usage === 'pieces' ? ($entry['weight'] / $entry['pieces']) * $multipl
                : $multipl  // precise
        );

        $nutrients = $entry['nutrients'];

        $perWeight = [
          'weight'    => round( $weight, 1),
          'calories'  => round( $entry['calories'] * ($weight / 100), 1),
          'nutrients' => [
            'fat'             => round( $nutrients['fat'] * ($weight / 100), 1),
            'saturatedFat'    => ! isset($nutrients['saturatedFat']) ? 0
                               : round( $nutrients['saturatedFat'] * ($weight / 100), 1),
            'monoUnsaturated' => ! isset($nutrients['monoUnsaturated']) ? 0
                               : round( $nutrients['monoUnsaturated'] * ($weight / 100), 1),
            'polyUnsaturated' => ! isset($nutrients['polyUnsaturated']) ? 0
                               : round( $nutrients['polyUnsaturated'] * ($weight / 100), 1),
            'carbs'           => round( $nutrients['carbs'] * ($weight / 100), 1),
            'sugar'           => round( $nutrients['fat']   * ($weight / 100), 1),
            'fibre'           => ! isset($nutrients['fibre']) ? 0
                               : round( $nutrients['fibre'] * ($weight / 100), 1),
            'amino'           => round( $nutrients['amino'] * ($weight / 100), 1),
            'salt'            => round( $nutrients['salt']  * ($weight / 100), 1)
          ],
          'price'     => isset($entry['price']) ? round( $entry['price'] * ($weight / $entry['weight']), 2) : 0
        ];


        // if( $food == 'Milch H' )  // DEBUG
        //   $debug = 'halt';

        unset($nutrients['fat']);
        unset($nutrients['saturatedFat']);
        unset($nutrients['monoUnsaturated']);
        unset($nutrients['polyUnsaturated']);
        unset($nutrients['carbs']);
        unset($nutrients['sugar']);
        unset($nutrients['fibre']);
        unset($nutrients['amino']);
        unset($nutrients['salt']);

        foreach( $nutrients as $name => $value)
          $perWeight['nutrients'][$name] = round( $nutrients[$name] * ($weight / 100), 1);

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
