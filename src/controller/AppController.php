<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/Controller_240323/ControllerBase.php';
require_once 'lib/ConfigStatic_240323/config.php';
require_once 'lib/User.php';
require_once 'lib/settings.php';
require_once 'ajax/save_day_entries.php';
// require_once 'ajax/save_foods.php';  // unused
require_once 'ajax/change_user.php';
require_once 'lib/helper.php';


class AppController extends ControllerBase
{
  use SaveDayEntriesAjaxController;
  use ChangeUserAjaxController;

  protected string     $mode;           // TASK: sort semantically
  protected string     $date;

  protected string     $dayEntriesTxt;
  protected array      $dayEntries;
  protected            $foodsView;
  protected float      $priceAvg;
  protected SimpleData $lastDaysView;

  protected array      $layout;
  protected array      $goals;
  protected array      $captions = [];
  protected SimpleData $inlineHelp;

  // TASK: MOVE

  protected array $nutrientsModel;
  protected array $nutrientsShort;


  public function __construct(/* $model = null, $view = null */)
  {
    parent::__construct();

    $config   = config::instance();
    $settings = settings::instance();

    // Help

    $this->inlineHelp = new SimpleData();
    $this->inlineHelp->set('app',   Yaml::parse( file_get_contents('misc/inline_help/app.yml')));
    $this->inlineHelp->set('foods', Yaml::parse( file_get_contents('misc/inline_help/foods.yml')));
  }


  public function render(/*$request*/)
  {
    $config = config::instance();

    $this->date = $_GET['date'] ?? date('Y-m-d');
    $this->mode = isset($_GET['date']) ? 'last' : 'current';

    $this->layout = parse_attribs('@attribs', ['short', '(i)'], Yaml::parse( file_get_contents('data/bundles/Default_JaneDoe@example.com-24080101000000/layouts/food.yml')));
    $this->goals  = Yaml::parse( file_get_contents('data/bundles/Default_JaneDoe@example.com-24080101000000/layouts/goals.yml'));

    foreach( $this->layout as $group => &$layout )
    {
      if( isset($layout['@attribs']['short']) )
        $layout['@attribs']['short'] = preg_replace('/\{(#[a-zA-Z0-9]+)\|([a-zA-Z0-9 ]+)\}/', '<a href="$1">$2</a>', $layout['@attribs']['short']);
    }

    // Day entries

    $this->dayEntriesTxt = trim( @file_get_contents('data/users/' . $config->get('defaultUser') . "/days/{$this->date}.tsv") ?: '', "\n");
    $this->dayEntries    = parse_tsv( $this->dayEntriesTxt );

    foreach( $this->dayEntries as $idx => $entry)
      $this->dayEntries[$idx][7] = Yaml::parse( $this->dayEntries[$idx][7] );

    // TASK: move in model

    $this->nutrientsModel['fattyAcids'] = Yaml::parse( file_get_contents('data/nutrients/fattyAcids.yml'));
    $this->nutrientsModel['aminoAcids'] = Yaml::parse( file_get_contents('data/nutrients/aminoAcids.yml'));
    $this->nutrientsModel['vitamins']   = Yaml::parse( file_get_contents('data/nutrients/vitamins.yml'));
    $this->nutrientsModel['minerals']   = Yaml::parse( file_get_contents('data/nutrients/minerals.yml'));
    $this->nutrientsModel['secondary']  = Yaml::parse( file_get_contents('data/nutrients/secondary.yml'));

    $this->nutrientsShort = [
      'nutritionalValues' => 'nutriVal',  // TASK: use short name from nutrient files
      'fattyAcids'        => 'fat',
      'aminoAcids'        => 'amino',
      'vitamins'          => 'vit',
      'minerals'          => 'min',
      'secondary'         => 'sec'
    ];

    $this->makeFoodsView();
    $this->makeNutrientsView();
    $this->makeDaysView();

    ob_start();
    require 'view/-this.php';
    return ob_get_clean();    // echo is done in index
  }

  private function makeFoodsView()
  {
    $settings = settings::instance();

    $this->model = new SimpleData();
    $this->model->set('foods', Yaml::parse( file_get_contents('data/bundles/Default_JaneDoe@example.com-24080101000000/foods.yml')));

    $this->foodsView = new SimpleData();

    // we pre calc all values (model data is a view)
    // cause it's simpler for recipes and less js code

    // TASK: maybe make more logical, e.g. var naming ... (did one round 2406)

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

      $usedAmounts = $foodEntry['usedAmounts'] ?? ( $settings->get("foods.defaultAmounts.$usage") ?: 1);

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

        // nutritional values for all nutrient groups

        foreach( $this->nutrientsShort as $group => $groupShort )    // all nutrient groups first (be sure we have at least an empty entry)
        {                                                            // (TASK) will be mod (see task nutrientsShort above)
          if( ! isset($foodEntry[$group]) || count($foodEntry[$group]) == 0)
            $perWeight[$groupShort] = [];
          else foreach( $foodEntry[$group] as $nutrient => $value )  // all the sub keys like nutritionalValues, minerals in food.yml
          {
            // if( $foodName == 'Salt' && $nutrient == 'salt' )      // DEBUG
            //   $debug = 'halt';

            $short = $group === 'nutritionalValues' ? $nutrient      // get short name from /nutrients
                   : $this->nutrientsModel[$group]['substances'][$nutrient]['short'];

            $perWeight[$groupShort][$short] = round( $value * ($weight / 100), 1);
          }
        }

        // $id = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $foodName));  // TASK: shorten
        $this->foodsView->set("foods.$foodName.$amount", $perWeight);
      }
    }
  }


  private function makeNutrientsView()
  {
    // TASK: make a sep view
    // TASK: group vals

    foreach(['fattyAcids', 'aminoAcids', 'vitamins', 'minerals', 'secondary'] as $group )
    {
      $this->captions[ $this->nutrientsShort[$group]] = $this->nutrientsModel[$group]['name'];

      foreach( $this->nutrientsModel[$group]['substances'] as $name => $attr )  // short is used as id
      {
        $a = $attr['amounts'][0];      // TASK: use unit for sth?

        $this->foodsView->set('nutrients.' . $this->nutrientsShort[$group] . ".$attr[short]", [
                                       
          'name'  => $name,            // TASK: (advanced) currently using first entry only
          'group' => $group,
          'lower' => strpos($a['lower'], '%') === false
                  ?  $a['amount'] - $a['lower']
                  :  $a['amount'] - $a['amount'] * (floatval($a['lower']) / 100),  // floatval removes the percent
          'ideal' => $a['amount'],
          'upper' => strpos($a['upper'], '%') === false
                  ?  $a['amount'] + $a['upper']
                  :  $a['amount'] + $a['amount'] * (floatval($a['upper']) / 100)
        ]);
      }
    }
  }


  private function makeDaysView()
  {
    $config   = config::instance();
    $settings = settings::instance();

    $this->lastDaysView = new SimpleData();
    $priceSumAll = 0; $days = 0;

    // TASK: remove current day

    foreach( scandir('data/users/' . $config->get('defaultUser') . '/days', SCANDIR_SORT_DESCENDING) as $file)
    {
      if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
        continue;
      
      $days++;

      $dat     = pathinfo( $file, PATHINFO_FILENAME);
      $entries = parse_tsv( file_get_contents('data/users/' . $config->get('defaultUser') . "/days/$file"));

      // foreach( $entries as $idx => $entry)
      //   $entries[$idx][7] = Yaml::parse( $entries[$idx][7] );
      
      // TASK: (advanced) unit from data?

      $this->lastDaysView->set( $dat, [
        'Calories'    => ( ! $entries ? 0 : array_sum( array_column($entries, 1))) . ' kcal',
        'Carbs'       => ( ! $entries ? 0 : array_sum( array_column($entries, 2))),
        'Fat'         => ( ! $entries ? 0 : array_sum( array_column($entries, 3))),
        'Amino acids' => ( ! $entries ? 0 : array_sum( array_column($entries, 4))) . ' g',
        'Salt'        => ( ! $entries ? 0 : array_sum( array_column($entries, 5))) . ' g',
        'Price'       => ( ! $entries ? 0 : array_sum( array_column($entries, 6))) . ' ' . $settings->get('currencySymbol')
      ]);
  
      $priceSumAll += ! $entries ? 0 : array_sum( array_column($entries, 6));
    }
    
    $this->priceAvg = ! $priceSumAll ? 0.0 : $priceSumAll / $days;
  }
}

?>
