<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/Controller_240323/ControllerBase.php';
require_once 'ajax/save_day_entries.php';
require_once 'ajax/save_foods.php';
require_once 'lib/helper.php';


class FoodsController extends ControllerBase
{
  use SaveDayEntriesAjaxController;
  use SaveFoodsAjaxController;

  private bool $devMode;

  private array  $recipes;
  private array  $foods;
  private string $foodsTxt;
  private array  $layout;
  private string $dayEntriesTxt;
  private array  $dayEntries;

  private SimpleData $inlineHelp;


  public function __construct(/*$model = null, $view = null*/)
  {
    parent::__construct();
  }


  /*@

  Make all the data

  */
  private function makeData()  /*@*/
  {
    $config = config::instance();

    $this->devMode = $config->get('devMode');

    $nutrients['fattyAcids'] = Yaml::parse( file_get_contents('data/nutrients/fattyAcids.yml'));
    $nutrients['aminoAcids'] = Yaml::parse( file_get_contents('data/nutrients/aminoAcids.yml'));
    $nutrients['vitamins']   = Yaml::parse( file_get_contents('data/nutrients/vitamins.yml'));
    $nutrients['minerals']   = Yaml::parse( file_get_contents('data/nutrients/minerals.yml'));
    $nutrients['secondary']  = Yaml::parse( file_get_contents('data/nutrients/secondary.yml'));

    $nutrientsShort = [
      'nutritionalValues' => 'nutriVal',  // TASK: use short name from nutrient files
      'fattyAcids'        => 'fat',
      'aminoAcids'        => 'amino',
      'vitamins'          => 'vit',
      'minerals'          => 'min',
      'secondary'         => 'sec'
    ];

    $this->foodsTxt = file_get_contents('data/foods.yml');
    $this->foods    = Yaml::parse( $this->foodsTxt );

    $this->recipes = [];

    $this->layout = Yaml::parse( file_get_contents('data/layout.yml'));

    $this->dayEntriesTxt = trim( @file_get_contents('data/users/' . $config->get('user') . "/days/{$this->date}.tsv") ?: '', "\n");
    $this->dayEntries    = parse_tsv( $this->dayEntriesTxt );

    foreach( $this->dayEntries as $idx => $entry)
      $this->dayEntries[$idx][7] = Yaml::parse( $this->dayEntries[$idx][7] );

    $this->inlineHelp = new SimpleData( Yaml::parse( file_get_contents('misc/inline_help.yml')));
    $this->model      = new SimpleData();


    // This day tab

    // we pre calc all values (model data is a view)
    // cause it's simpler for recipes and less js code

    // make food list with amounts (model)

    foreach( $this->foods as $food => $entry )
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
        // if( $food == 'Salt' )  // DEBUG
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

        foreach( $nutrientsShort as $group => $sgroup )
        {
          if( ! isset($entry[$group]) || count($entry[$group]) == 0)
            $perWeight[$sgroup] = [];
          else
            foreach( $entry[$group] as $name => $value)
            {
              // if( $food == 'Salt' && $name == 'salt' )  // DEBUG
              //   $debug = 'halt';

              $short = $group === 'nutritionalValues' ? $name
                     : $nutrients[$group][$name]['short'];

              $perWeight[$sgroup][$short] = round( $value * ($weight / 100), 1);
            }
        }

        $title = str_replace('.', ',', $amount);  // be compatible with amounts like 1.38
        $title = str_pad( $title, 5, ' ', STR_PAD_LEFT) . " $food";  // TASK: improve

        $this->model->set("foods.$title", $perWeight);
      }
    }


    // Summary tab
    // TASK: make model usage somewhat more logical
    // TASK: group vals

    foreach(['fattyAcids', 'aminoAcids', 'vitamins', 'minerals', 'secondary'] as $group )
    {
      $this->model->set("nutrients.$nutrientsShort[$group].name", $nutrients[$group]['name']);

      foreach( $nutrients[$group] as $name => $attr )  // short is used as id
      {
        if( in_array( $name, ['short', 'name', 'unit', 'per', 'comment', 'amounts']))
          continue;

        $a = $attr['amounts'][0];      // TASK: use unit for sth?

        $this->model->set("nutrients.$nutrientsShort[$group].$attr[short]", [
                                       
          'name'  => $name,            // TASK: (advanced) currently using first entry only
          'group' => $group,
          'lower' => strpos($a['lower'], '%') === false
                  ?  $a['amount'] - $a['lower']
                  :  $a['amount'] - $a['amount'] * (floatval($a['lower']) / 100),  // floatval removes the percent
          'ideal' => $a['amount'],
          'upper' => strpos($a['lower'], '%') === false
                  ?  $a['amount'] + $a['upper']
                  :  $a['amount'] + $a['amount'] * (floatval($a['upper']) / 100)   // TASK: prefer calc or just write the val?
        ]);
      }
    }

    // All days tab
    // no model data, kind of report

    foreach( scandir('data/users/' . $config->get('user') . '/days', SCANDIR_SORT_DESCENDING) as $file)
    {
      if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
        continue;

      $dat     = pathinfo($file, PATHINFO_FILENAME);
      $entries = parse_tsv( file_get_contents('data/users/' . $config->get('user') . "/days/$file"));

      // foreach( $entries as $idx => $entry)
      //   $entries[$idx][7] = Yaml::parse( $entries[$idx][7] );

      $this->lastDaysSums[$dat] = [
        'caloriesSum' => ! $entries ? 0 : array_sum( array_column($entries, 1)),
        'carbsSum'    => ! $entries ? 0 : array_sum( array_column($entries, 2)),
        'fatSum'      => ! $entries ? 0 : array_sum( array_column($entries, 3)),
        'aminoSum'    => ! $entries ? 0 : array_sum( array_column($entries, 4)),
        'saltSum'     => ! $entries ? 0 : array_sum( array_column($entries, 5)),
        'priceSum'    => ! $entries ? 0 : array_sum( array_column($entries, 6))
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
