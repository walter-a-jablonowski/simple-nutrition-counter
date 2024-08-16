<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/frm/Controller_240323/ControllerBase.php';
require_once 'lib/frm/ConfigStatic_240323/config.php';
require_once 'lib/frm/User.php';
require_once 'lib/settings.php';
require_once 'ajax/save_day_entries.php';
require_once 'ajax/change_user.php';
require_once 'lib/helper.php';


class AppController extends ControllerBase
{
  use SaveDayEntriesAjaxController;
  use ChangeUserAjaxController;

  protected string     $mode;
  protected string     $date;

  protected SimpleData $nutrientsModel;
  protected SimpleData $foodsModel;

  protected string     $dayEntriesTxt;   // edit tab
  protected array      $dayEntries;
  protected SimpleData $foodsView;
  protected array      $layout;

  protected SimpleData $nutrientsView;   // nutrients tab, last days
  protected float      $priceAvg;
  protected SimpleData $lastDaysView;

  protected array      $captions = [];
  protected SimpleData $inlineHelp;


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
    $user   = User::current();

    $this->date = $_GET['date'] ?? date('Y-m-d');  // TASK: (advanced) is request
    $this->mode = isset($_GET['date']) ? 'last' : 'current';

    // Model
    // TASK: maybe also use -this > calories

    $this->foodsModel = new SimpleData();
    $this->foodsModel->setData( Yaml::parse( file_get_contents("data/bundles/Default_$user->id/foods.yml")));

    $this->nutrientsModel = new SimpleData();  // TASK: (advanced) merge with bundle /nutrients

    foreach(['fattyAcids', 'carbs', 'aminoAcids', 'vitamins', 'minerals', 'secondary'] as $type)
    {
      $this->nutrientsModel->set( $type,
        Yaml::parse( file_get_contents("data/nutrients/$type.yml"))
      );
    }

    // Edit tab: This day

    $this->dayEntriesTxt = trim( @file_get_contents('data/users/' . $config->get('defaultUser') . "/days/{$this->date}.tsv") ?: '', "\n");
    $this->dayEntries    = parse_tsv( $this->dayEntriesTxt );

    foreach( $this->dayEntries as $idx => $entry)
      $this->dayEntries[$idx][7] = Yaml::parse( $this->dayEntries[$idx][7] );

    // Edit tab: Food list

    $this->makeFoodsView();

    $this->layout = parse_attribs('@attribs', ['short', '(i)'],
      Yaml::parse( file_get_contents("data/bundles/Default_$user->id/layouts/food.yml"))
    );
    
    foreach( $this->layout as $group => &$layout )
    {
      if( isset($layout['@attribs']['short']) )
        $layout['@attribs']['short'] = preg_replace('/\{(#[a-zA-Z0-9]+)\|([a-zA-Z0-9 ]+)\}/', '<a href="$1">$2</a>', $layout['@attribs']['short']);
    }

    // Nutrients tab

    $this->makeNutrientsView();

    // Last days tab

    $this->makeLastDaysView();

    ob_start();
    require 'view/-this.php';
    return ob_get_clean();
  }

  /*@

  makeFoodsView()

  - pre calc all food and recipes nutritional values per amount used
  - easy print in food grid, less js logic

  */
  private function makeFoodsView()  /*@*/
  {
    $settings = settings::instance();
    $user     = User::current();

    $this->foodsView = new SimpleData();

    foreach( $this->foodsModel->all() as $name => $data )
    {
      $data['weight'] = trim( $data['weight'], "mgl ");  // just for convenience, we don't need the unit here

      $usage = isset( $data['usedAmounts']) && (
                 strpos( $data['usedAmounts'][0], 'g')  !== false ||
                 strpos( $data['usedAmounts'][0], 'ml') !== false
               )
             ? 'precise' : (
               isset($data['pieces'])
             ? 'pieces'
             : 'pack'
      );

      $usedAmounts = $data['usedAmounts'] ?? ( $settings->get("foods.defaultAmounts.$usage") ?: 1);

      foreach( $usedAmounts as $amount )
      {
        // if( $name == 'Salt' )  // DEBUG
        //   $debug = 'halt';

        $multipl = trim( $amount, "mglpc ");
        $multipl = (float) eval("return $multipl;");  // 1/2 => 0.5, or: eval("\$multipl = $multipl;")

        $weight = $usage === 'pack'   ? $data['weight'] * $multipl : (
                  $usage === 'pieces' ? ($data['weight'] / $data['pieces']) * $multipl
                : $multipl  // precise
        );

        $perWeight = [
          'weight'   => round( $weight, 1),
          'calories' => round( $data['calories'] * ($weight / 100), 1),
          'price'    => isset( $data['price']) ? round( $data['price'] * ($weight / $data['weight']), 2) : 0
        ];

        // nutritional values for all nutrient groups

        $nutrientGroups = array_merge(['nutritionalValues'], array_keys( $this->nutrientsModel->all()));
        
        foreach( $nutrientGroups as $groupName )
        {
          $shortName = $groupName === 'nutritionalValues' ? 'nutriVal'
                     : $this->nutrientsModel->get("$groupName.short");

          if( ! isset($data[$groupName]) || count($data[$groupName]) == 0)
            $perWeight[$shortName] = [];
          else foreach( $data[$groupName] as $nutrient => $value )
          {
            // if( $name == 'Salt' && $nutrient == 'salt' )              // DEBUG
            //   $debug = 'halt';

            $short = $groupName === 'nutritionalValues' ? $nutrient      // short name for single nutrient
                   : $this->nutrientsModel->get("$groupName.substances.$nutrient.short");

            $perWeight[$shortName][$short] = round( $value * ($weight / 100), 1);
          }
        }

        $this->foodsView->set("$name.$amount", $perWeight);
        // $id = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $name));  // TASK: shorten
      }
    }
  }

  /*@

  makeNutrientsView()

  - pre calc all food and recipes recommended amounts per day
  - easy print in food grid, less js logic

  */
  private function makeNutrientsView()  /*@*/
  {
    // TASK: group vals

    $this->nutrientsView = new SimpleData();

    foreach(['fattyAcids', 'carbs', 'aminoAcids', 'vitamins', 'minerals', 'secondary'] as $group )
    {
      $shortName = $this->nutrientsModel->get("$group.short");
      $this->captions[$shortName] = $this->nutrientsModel->get("$group.name");

      foreach( $this->nutrientsModel->get("$group.substances") as $name => $attr )  // short is used as id
      {
        $a = $attr['amounts'][0];  // TASK: use unit for sth?

        $this->nutrientsView->set("$shortName.$attr[short]", [  // TASK: is that right?
                                       
          'name'  => $name,        // TASK: (advanced) currently using first entry only
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


  private function makeLastDaysView()
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
      
      // TASK: upd this a bit?
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
