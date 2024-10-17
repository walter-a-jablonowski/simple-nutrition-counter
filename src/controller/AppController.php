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

  const DAY_HEADERS     = ['time', 'type', 'food', 'calories', 'fat', 'carbs', 'amino', 'salt', 'price', 'nutrients'];
  const NUTRIENT_GROUPS = ['fattyAcids', 'carbs', 'aminoAcids', 'vitamins', 'minerals', 'secondary', 'misc'];

  protected string     $mode;
  protected string     $date;

  protected SimpleData $nutrientsModel;
  protected SimpleData $foodsModel;

  protected string     $dayEntriesTxt;   // edit tab
  protected array      $dayEntries;
  protected array      $layout;
  protected SimpleData $layoutView;

  protected SimpleData $nutrientsView;   // nutrients tab, last days
  protected array      $priceAvg;
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

    // Nutrients model

    $this->nutrientsModel = new SimpleData();  // TASK: (advanced) merge with bundle /nutrients
    
    foreach( self::NUTRIENT_GROUPS as $groupName )
    {
      $this->nutrientsModel->set( $groupName,
        Yaml::parse( file_get_contents("data/bundles/Default_$user->id/nutrients/$groupName.yml"))
      );
    }

    // Food model
    // TASK: maybe also use -this > calories

    $this->foodsModel = new SimpleData();
    
    $dir     = "data/bundles/Default_$user->id/foods";
    $exclude = ['.', '..', '-this.yml', '-this_SAV.yml', '_blank_food.yml'];

    foreach( scandir($dir) as $file )
    {
      if( in_array( $file, $exclude) || ( pathinfo($file, PATHINFO_EXTENSION) !== 'yml' && ! is_dir("$dir/$file")))
        continue;

      $name = is_dir("$dir/$file")  ?  $file  :  pathinfo($file, PATHINFO_FILENAME);
      $food = is_file("$dir/$file")
            ? Yaml::parse( file_get_contents("$dir/$file"))
            : Yaml::parse( file_get_contents("$dir/$file/-this.yml"));

      // merge nutrients from food file (prio) over default foods
      // TASK: maybe we want to add at least an empty key if a type of nutrients is missing

      // if( $name == 'Chick R Bio' )  // DEBUG
      //   $debug = 'halt';

      if( isset( $food['subType']) && file_exists("data/food_defaults/$food[subType].yml"))
      {
        $nutrients = Yaml::parse( file_get_contents("data/food_defaults/$food[subType].yml"));
        
        foreach( self::NUTRIENT_GROUPS as $groupName )
        {
          if( isset( $nutrients[$groupName] ))
            $food[$groupName] = array_merge( $nutrients[$groupName], $food[$groupName] ?? []);
          // else
          //   // $food[$groupName] = $nutrients[$groupName];
          //   $food[$groupName] = $food[$groupName] ?? [];
        }
      }

      $this->foodsModel->set( $name, $food );
    }

    // Edit tab: Day entries

    $this->dayEntriesTxt = trim( @file_get_contents('data/users/' . $config->get('defaultUser') . "/days/{$this->date}.tsv") ?: '', "\n");
    $this->dayEntries    = parse_tsv( $this->dayEntriesTxt, self::DAY_HEADERS );
    
    foreach( $this->dayEntries as $idx => &$entry )
      $entry['nutrients'] = Yaml::parse( $entry['nutrients'] );

    unset($entry);  // needed cause in a later `<?php foreach( $this->dayEntries as $entry ): ? >`
                    // entry still exists as ref, which means the last entry gets replaced with the data of the first

    // foreach( $this->dayEntries as $idx => $entry )
    //   $this->dayEntries[$idx]['nutrients'] = Yaml::parse( $this->dayEntries[$idx]['nutrients'] );

    // Edit tab: Food list

    $this->makeLayoutView();
    $this->layout = Yaml::parse( file_get_contents("data/bundles/Default_$user->id/layout.yml"));
    
    foreach( $this->layout as $tab => $layout )
    {
      // if( $tab === 'On the go')
      //   $debug = 'halt';

      $this->layout[$tab] =
        $layout = parse_attribs('@attribs', ['short', '(i)'], $layout);
    
      foreach( $layout as $group => $entries )
      {
        if( isset($entries['@attribs']['short']) )
        {
          $this->layout[$tab][$group]['@attribs']['short'] =
            preg_replace('/\{(#[a-zA-Z0-9]+)\|([a-zA-Z0-9 ]+)\}/', '<a href="$1">$2</a>', $entries['@attribs']['short']);
        }
      }
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

  makeLayoutView()

  - pre calc all amounts
  - easy print in food grid, less js logic

  */
  private function makeLayoutView()  /*@*/
  {
    // TASK: add types for user > misc
    // - type field
    // - load data

    $settings = settings::instance();
    $user     = User::current();

    $this->layoutView = new SimpleData();

    foreach( $this->foodsModel->all() as $name => $data )
    {
      // print "$name<br>";   // DEBUG

      // if( $name == 'Chick R Bio')
      //   $debug = 'halt';

      $data['weight'] = trim( $data['weight'], "mgl ");  // just for convenience, we don't need the unit here

      $usage = isset( $data['usedAmounts']) && (
                 strpos( $data['usedAmounts'][0], 'g')  !== false ||
                 strpos( $data['usedAmounts'][0], 'ml') !== false
               )
             ? 'precise' : (
               isset($data['pieces' ])
             ? 'pieces'
             : 'pack'
      );

      $usedAmounts = $data['usedAmounts'] ?? ( $settings->get("foods.defaultAmounts.$usage") ?: 1);

      foreach( $usedAmounts as $amount )
      {
        // if( $name == 'Toasties R Bio' )  // DEBUG
        //   $debug = 'halt';

        $multipl = trim( $amount, "mglpc ");
        $multipl = (float) eval("return $multipl;");  // 1/2 => 0.5 or: eval("\$multipl = $multipl;")

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

        foreach( array_merge(['nutritionalValues'], self::NUTRIENT_GROUPS) as $groupName )
        {
          $shortName = $groupName === 'nutritionalValues' ? 'nutriVal'
                     : $this->nutrientsModel->get("$groupName.short");

          $perWeight[$shortName] = [];

          if( isset($data[$groupName]) && count($data[$groupName]) > 0)
            foreach( $data[$groupName] as $nutrient => $value )
            {
              if( $groupName != 'nutritionalValues' && ! $this->nutrientsModel->has("$groupName.substances.$nutrient"))
                continue;

              // if( $groupName != 'nutritionalValues' )          // DEBUG
              //   $debug = 'halt';

              $short = $groupName === 'nutritionalValues' ? $nutrient  // short name for single nutrient
                     : $this->nutrientsModel->get("$groupName.substances.$nutrient.short");

              $perWeight[$shortName][$short] = round( $value * ($weight / 100), 1);
            }
        }

        $this->layoutView->set("$name.$amount", $perWeight);
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

    foreach( self::NUTRIENT_GROUPS as $groupName )
    {
      $shortName = $this->nutrientsModel->get("$groupName.short");
      $this->captions[$shortName] = $this->nutrientsModel->get("$groupName.name");

      foreach( $this->nutrientsModel->get("$groupName.substances") as $name => $attr )  // short is used as id
      {
        $a = $attr['amounts'][0];

        $this->nutrientsView->set("$shortName.$attr[short]", [
                                       
          'name'  => $name,        // TASK: (advanced) currently using first entry only
          'group' => $groupName,
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
    $data = [];
    
    foreach( scandir('data/users/' . $config->get('defaultUser') . '/days', SCANDIR_SORT_DESCENDING) as $file)
    {
      if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
        continue;

      $dat = pathinfo($file, PATHINFO_FILENAME);
      $entries = parse_tsv( file_get_contents('data/users/' . $config->get('defaultUser') . "/days/$file"), self::DAY_HEADERS);

      // foreach( $entries as $idx => $entry)
      //   $entries[$idx][7] = Yaml::parse( $entries[$idx][7] );

      $data[$dat] = $entries;

      $this->lastDaysView->set( $dat, [
        'calories' => ( ! $entries ? 0 : array_sum( array_column($entries, 'calories'))),
        'fat'      => ( ! $entries ? 0 : array_sum( array_column($entries, 'fat'))),
        'carbs'    => ( ! $entries ? 0 : array_sum( array_column($entries, 'carbs'))),
        'amino'    => ( ! $entries ? 0 : array_sum( array_column($entries, 'amino'))),
        'salt'     => ( ! $entries ? 0 : array_sum( array_column($entries, 'salt'))),
        'price'    => ( ! $entries ? 0 : array_sum( array_column($entries, 'price')))
      ]);
    }
      
    $priceSumAll = [7 => 0, 15 => 0, 30 => 0];
    $currentDate = new DateTime();  // TASK: also look if cuurent dat is in data so that we have current data

    if( count($data) >= 7 )
      foreach( $data as $dat => $entries )
      {
        $fileDate = new DateTime($dat);
        $daysDiff = $currentDate->diff($fileDate)->days;
        $dayPrice = array_sum( array_column($entries, 'price'));

        if( count($data) >= 7  && $daysDiff < 7 )
          $priceSumAll[7] += $dayPrice;

        if( count($data) >= 15 && $daysDiff < 15 )
          $priceSumAll[15] += $dayPrice;
        
        if( count($data) >= 30 && $daysDiff < 30 )
          $priceSumAll[30] += $dayPrice;
        else  break;
      }

    $this->priceAvg = [
      'week'   => ! $priceSumAll[7]  ? 'n/a' : number_format( $priceSumAll[7]  / 7,  2) . ' ' . $settings->get('currencySymbol'),
      '15days' => ! $priceSumAll[15] ? 'n/a' : number_format( $priceSumAll[15] / 15, 2) . ' ' . $settings->get('currencySymbol'),
      '30days' => ! $priceSumAll[30] ? 'n/a' : number_format( $priceSumAll[30] / 30, 2) . ' ' . $settings->get('currencySymbol')
    ];
  }
}

?>
