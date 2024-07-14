<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/Controller_240323/ControllerBase.php';
require_once 'ajax/save_day_entries.php';
// require_once 'ajax/save_foods.php';  // unused
require_once 'ajax/change_user.php';
require_once 'lib/helper.php';


class FoodsController extends ControllerBase
{
  use SaveDayEntriesAjaxController;
  // use SaveFoodsAjaxController;  // unused
  use ChangeUserAjaxController;

  protected SimpleData $config;
  protected SimpleData $settings;
  protected string     $user;
  protected array      $users = [];
  protected bool       $devMode;   // TASK: rm

  protected string     $mode;
  protected string     $date;

  protected            $modelView;
  protected string     $dayEntriesTxt;
  protected array      $dayEntries;
  // protected string  $foodsTxt;   // old
  protected array      $lastDaysSums;
  protected float      $priceAvg;

  protected array      $layout;
  protected array      $captions = [];
  protected SimpleData $inlineHelp;


  public function __construct(/*$model = null, $view = null*/)
  {
    parent::__construct();
  }


  /*@

  Make all the data

  */
  private function makeData()  /*@*/
  {
    $config = $this->config = config::instance();
    $this->devMode = $config->get('devMode');  // TASK: just use config in view

    // TASK: move settings?
    $this->settings = new SimpleData( $config->get('defaultSettings'));  // TASK: (advanced) merge user settings

    // TASK: simple version of user mngm (mov in index ?)

    $users = array_filter( scandir('data/users'),
      fn($fil) => is_dir("data/users/$fil") && ! in_array( $fil, ['.', '..'])
    );

    foreach( $users as $user )
      $this->users[$user] = Yaml::parse( file_get_contents("data/users/$user/-this.yml"))['name'];

    $_SESSION['user'] = $_SESSION['user'] ?? 'single_user';
    $this->user = $_SESSION['user'];
    $this->userName = Yaml::parse( file_get_contents('data/users/' . $this->user . '/-this.yml'))['name'];

    $this->layout     = parse_attribs('@attribs', ['short', '(i)'], Yaml::parse( file_get_contents('data/bundles/Veggie_DESouth_1/layout.yml')));
    $this->inlineHelp = new SimpleData();
    $this->inlineHelp->set('app',   Yaml::parse( file_get_contents('misc/inline_help/app.yml')));
    $this->inlineHelp->set('foods', Yaml::parse( file_get_contents('misc/inline_help/foods.yml')));

    $this->dayEntriesTxt = trim( @file_get_contents('data/users/' . $config->get('user') . "/days/{$this->date}.tsv") ?: '', "\n");
    $this->dayEntries    = parse_tsv( $this->dayEntriesTxt );

    foreach( $this->dayEntries as $idx => $entry)
      $this->dayEntries[$idx][7] = Yaml::parse( $this->dayEntries[$idx][7] );

    // $this->foodsTxt = file_get_contents('data/bundles/Veggie_DESouth_1/foods.yml');  // old

    $this->model = new SimpleData();
    $this->model->set('foods', Yaml::parse( file_get_contents('data/bundles/Veggie_DESouth_1/foods.yml')));

    $this->modelView = new SimpleData();

    // TASK: move in model
    $nutrientsModel['fattyAcids'] = Yaml::parse( file_get_contents('data/nutrients/fattyAcids.yml'));
    $nutrientsModel['aminoAcids'] = Yaml::parse( file_get_contents('data/nutrients/aminoAcids.yml'));
    $nutrientsModel['vitamins']   = Yaml::parse( file_get_contents('data/nutrients/vitamins.yml'));
    $nutrientsModel['minerals']   = Yaml::parse( file_get_contents('data/nutrients/minerals.yml'));
    $nutrientsModel['secondary']  = Yaml::parse( file_get_contents('data/nutrients/secondary.yml'));

    $nutrientsShort = [
      'nutritionalValues' => 'nutriVal',  // TASK: use short name from nutrient files
      'fattyAcids'        => 'fat',
      'aminoAcids'        => 'amino',
      'vitamins'          => 'vit',
      'minerals'          => 'min',
      'secondary'         => 'sec'
    ];


    // This day tab

    // we pre calc all values (model data is a view)
    // cause it's simpler for recipes and less js code

    // TASK: maybe make more logical, e.g. var naming ... (did one round 2406)

    // make food list with amounts (model)

    // foreach( Yaml::parse( file_get_contents('data/bundles/Veggie_DESouth_1/foods.yml')) as $foodName => $foodEntry )
    foreach( $this->model->get('foods') as $foodName => $foodEntry )
    {
      $foodEntry['weight'] = trim( $foodEntry['weight'], "mgl ");  // just for convenience, we don't need the unit here

      $usage = isset( $foodEntry['usedAmounts']) && (
                 strpos( $foodEntry['usedAmounts'][0], 'g')  !== false ||
                 strpos( $foodEntry['usedAmounts'][0], 'ml') !== false
               )
             ? 'precise' : (
               isset($foodEntry['pieces'])
             ? 'pieces'
             : 'pack'
      );

      // $usedAmounts = $foodEntry['usedAmounts'] ?? ( $config->get("foods.defaultAmounts.$usage") ?: 1);
      $usedAmounts = $foodEntry['usedAmounts'] ?? ( $this->settings->get("foods.defaultAmounts.$usage") ?: 1);

      foreach( $usedAmounts as $amount )
      {
        // if( $foodName == 'Salt' )  // DEBUG
        //   $debug = 'halt';

        $multipl = trim( $amount, "mglpc ");
        $multipl = (float) eval("return $multipl;");  // 1/2 => 0.5
        // eval("\$multipl = $multipl;");

        $weight = $usage === 'pack'   ? $foodEntry['weight'] * $multipl : (
                  $usage === 'pieces' ? ($foodEntry['weight'] / $foodEntry['pieces']) * $multipl
                : $multipl  // precise
        );

        $perWeight = [
          'weight'   => round( $weight, 1),
          'calories' => round( $foodEntry['calories'] * ($weight / 100), 1),
          'price'    => isset( $foodEntry['price']) ? round( $foodEntry['price'] * ($weight / $foodEntry['weight']), 2) : 0
        ];

        // nutritinal values for all nutrient groups

        foreach( $nutrientsShort as $group => $groupShort )          // all nutrient groups first (be sure we have at least an empty entry)
        {                                                            // (TASK) will be mod (see task nutrientsShort above)
          if( ! isset($foodEntry[$group]) || count($foodEntry[$group]) == 0)
            $perWeight[$groupShort] = [];
          else foreach( $foodEntry[$group] as $nutrient => $value )  // all the sub keys like nutritionalValues, minerals in food.yml
          {
            // if( $foodName == 'Salt' && $nutrient == 'salt' )      // DEBUG
            //   $debug = 'halt';

            $short = $group === 'nutritionalValues' ? $nutrient      // get short name from /nutrients
                   : $nutrientsModel[$group]['substances'][$nutrient]['short'];

            $perWeight[$groupShort][$short] = round( $value * ($weight / 100), 1);
          }
        }

        // $id = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $foodName));  // TASK: shorten
        $this->modelView->set("foods.$foodName.$amount", $perWeight);
      }
    }


    // Nutrients tab
    // TASK: group vals

    foreach(['fattyAcids', 'aminoAcids', 'vitamins', 'minerals', 'secondary'] as $group )
    {
      $this->captions[ $nutrientsShort[$group]] = $nutrientsModel[$group]['name'];

      foreach( $nutrientsModel[$group]['substances'] as $name => $attr )  // short is used as id
      {
        $a = $attr['amounts'][0];      // TASK: use unit for sth?

        $this->modelView->set("nutrients.$nutrientsShort[$group].$attr[short]", [
                                       
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

    $priceSumAll = 0; $days = 0;
    
    foreach( scandir('data/users/' . $config->get('user') . '/days', SCANDIR_SORT_DESCENDING) as $file)
    {
      if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
        continue;
      
      $days++;

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
  
      $priceSumAll += ! $entries ? 0 : array_sum( array_column($entries, 6));
    }
    
    $this->priceAvg = ! $priceSumAll ? 0.0 : $priceSumAll / $days;
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
