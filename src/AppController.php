<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/frm/Controller_240323/ControllerBase.php';
require_once 'lib/frm/ConfigStatic_240323/config.php';
require_once 'lib/frm/User.php';
require_once 'lib/settings.php';

require_once 'data_view/LayoutView.php';
require_once 'data_view/NutrientsView.php';
require_once 'data_view/LastDaysView.php';

foreach( scandir('ajax') as $fil)
  if( ! in_array( $fil, ['.', '..']))  require_once "ajax/$fil";

require_once 'lib/helper.php';


class AppController extends ControllerBase
{
  use LayoutView;
  use NutrientsView;
  use LastDaysView;

  use SaveDayEntriesAjaxController;
  use ChangeUserAjaxController;
  use SavePriceAjaxController;

  const DAY_HEADERS     = ['time', 'type', 'food', 'calories', 'fat', 'carbs', 'amino', 'salt', 'price', 'nutrients'];
  const NUTRIENT_GROUPS = ['lipids/fattyAcids', 'carbs', 'aminoAcids', 'vitamins', 'minerals', 'secondary', 'misc'];

  protected string     $mode;
  protected string     $date;

  protected SimpleData $nutrientsModel;
  protected SimpleData $foodsModel;
  protected SimpleData $supplementsModel;
  protected SimpleData $combinedModel;

  protected string     $dayEntriesTxt;   // edit tab
  protected array      $dayEntries;
  protected array      $layout;


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


  public function render(/* $request */)
  {
    $config = config::instance();
    $user   = User::current();

    $this->date = $_GET['date'] ?? date('Y-m-d');  // TASK: (advanced) is request
    $this->mode = 'current';
    
    if( isset($_GET['date'])) {
      $currentDate = date('Y-m-d');
      $paramDate   = $_GET['date'];
      
      if( $paramDate < $currentDate )
        $this->mode = 'last';
      elseif( $paramDate > $currentDate )
        $this->mode = 'next';
      else
        $this->mode = 'current';
    }

    // Nutrients model

    $this->nutrientsModel = new SimpleData();

    foreach( self::NUTRIENT_GROUPS as $groupName )
    {
      $this->nutrientsModel->set( $groupName,
        Yaml::parse( file_get_contents("data/bundles/Default_$user->id/nutrients/$groupName.yml"))
      );
    }

    // Food model

    $this->foodsModel = new SimpleData();

    $dir = "data/bundles/Default_$user->id/foods";

    foreach( scandir($dir) as $file )
    {
      if( in_array( $file, ['.', '..']) || in_array( $file[0], ['_']) || ( pathinfo($file, PATHINFO_EXTENSION) !== 'yml' && ! is_dir("$dir/$file")))
        continue;

      $name = is_dir("$dir/$file")  ?  $file  :  pathinfo($file, PATHINFO_FILENAME);
      $food = is_file("$dir/$file")
            ? Yaml::parse( file_get_contents("$dir/$file"))
            : Yaml::parse( file_get_contents("$dir/$file/-this.yml"));

      // merge nutrients from food file (prio) over default foods
      // TASK: maybe we want to add at least an empty key if a type of nutrients is missing

      if( isset( $food['type']) && file_exists("data/food_defaults/$food[type].yml"))
      {
        $nutrients = Yaml::parse( file_get_contents("data/food_defaults/$food[type].yml"));

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

    // Supplements

    $this->supplementsModel = new SimpleData();

    $dir = "data/bundles/Default_$user->id/supplements";

    foreach( scandir($dir) as $file )
    {
      if( in_array( $file, ['.', '..']) || in_array( $file[0], ['_']) || ( pathinfo($file, PATHINFO_EXTENSION) !== 'yml' && ! is_dir("$dir/$file")))
        continue;

      $name  = is_dir("$dir/$file")  ?  $file  :  pathinfo($file, PATHINFO_FILENAME);
      $suppl = is_file("$dir/$file")
            ? Yaml::parse( file_get_contents("$dir/$file"))
            : Yaml::parse( file_get_contents("$dir/$file/-this.yml"));

      $this->supplementsModel->set( $name, $suppl );
    }

    // Combined model (used in view)

    $this->combinedModel = new SimpleData( $this->foodsModel->all(), $this->supplementsModel->all() );

    // Edit tab: Day entries

    $this->dayEntriesTxt = trim( @file_get_contents('data/users/' . $config->get('defaultUser') . "/days/{$this->date}.tsv") ?: '', "\n");
    $this->dayEntries    = parse_tsv( $this->dayEntriesTxt, self::DAY_HEADERS );

    foreach( $this->dayEntries as $idx => &$entry )
      $entry['nutrients'] = Yaml::parse( $entry['nutrients'] );

    unset($entry);  // needed cause in a later `<?php foreach( $this->dayEntries as $entry ): ? >`
                    // entry still exists as ref, which means the last entry gets replaced with the data of the first

    // Edit tab: Food list

    $this->makeLayoutView();
    $this->layout = Yaml::parse( file_get_contents("data/bundles/Default_$user->id/layout.yml"));

    foreach( $this->layout as $tab => $layout )
    {
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
}

?>
