<?php

/*

Standalone tests for the nutrient bounds and the ranges behind the signal color of
the quick summary widgets (see NutrientsView).
Run from the `src` directory:  php tools/test_widget_ranges.php

*/

use Symfony\Component\Yaml\Yaml;

chdir( dirname(__DIR__));

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

// Stub of the app's User: settings.yml as User::byId() loads it

class User
{
  public static function current( ?string $key = null )
  {
    $settings = Yaml::parse( file_get_contents('data/users/JaneDoe@example.com-24080101000000/settings.yml'));
    $data     = new SimpleData(['settings' => $settings]);

    return is_null($key) ? $data : $data->get($key);
  }
}

require_once 'models/NutrientsView.php';

// Minimal host for the trait, filled with the real nutrients data

class WidgetRangeTest
{
  use NutrientsView;

  const NUTRIENT_GROUPS = ['lipids/fattyAcids', 'carbs', 'aminoAcids', 'vitamins', 'minerals', 'secondary', 'misc'];

  public array $captions = [];   // filled by makeNutrientsView(), lives in AppController

  public function __construct()
  {
    $bundle = 'data/bundles/Default_JaneDoe@example.com-24080101000000';

    $this->nutrientsModel = new SimpleData();

    foreach( self::NUTRIENT_GROUPS as $groupName )
      $this->nutrientsModel->set( $groupName, Yaml::parse( file_get_contents("$bundle/nutrients/$groupName.yml")));

    foreach( Yaml::parse( file_get_contents("$bundle/nutrients/-this.yml")) as $name => $nutrient )
      $this->nutrientsModel->set( $name, $nutrient );

    $this->makeNutrientsView();
    $this->makeWidgetRanges();
  }

  public function nutrient( string $key ) : ?array
  {
    return $this->nutrientsView->get( $key );
  }

  public function bound( $amount, $bound, $isUpper = false ) : float
  {
    return $this->calculateBound( $amount, $bound, $isUpper );
  }

  public function range( string $metric ) : ?array
  {
    return $this->widgetRanges[$metric] ?? null;
  }

  public function attribs( string $metric ) : string
  {
    return $this->rangeAttribs( $metric );
  }
}

$t = new WidgetRangeTest();

// 1) calculateBound: a percentage is a tolerance around the ideal amount

check('percentage lower', $t->bound( 2200, '5%')        === 2090.0);
check('percentage upper', $t->bound( 2200, '5%',  true) === 2310.0);
check('zero percentage',  $t->bound( 80,   '0%',  true) === 80.0);

// 2) calculateBound: an absolute value is the bound itself, not a tolerance
//    (all data files use it that way: lower < amount < upper, e.g. salt 4 / 5 / 6)

check('absolute lower', $t->bound( 5,     4,     false) === 4.0);
check('absolute upper', $t->bound( 5,     6,     true)  === 6.0);
check('absolute fraction upper', $t->bound( 0.065, 0.1, true) === 0.1);

// 3) Ranges of the widget values, read from the real data files

$expected = [
  'calories'   => [2090.0, 2310.0],  // -this.yml, regular day (not the reduced / fillUp variants)
  'fat'        => [61.0,   86.0],    // lipids/fattyAcids.yml, group level, 25 - 35% of the calories
  'amino'      => [57.0,   63.0],    // aminoAcids.yml, group level, not the workout variant
  'carbs'      => [0.0,    130.0],   // carbs.yml, group level
  'sugar'      => [0.0,    25.0],    // carbs.yml > substances > Sugar, 0 with a tolerance
  'fibre'      => [25.0,   50.0],    // carbs.yml > substances > Fibre
  'salt'       => [4.0,    6.0],     // minerals.yml > substances > Salt
  'water'      => [2000.0, 4000.0],  // misc.yml > substances > water
  'price'      => [0,      7],       // user settings
  'eatingTime' => [0,      6]        // user settings
];

foreach( $expected as $metric => [$lower, $upper] )
{
  $range = $t->range($metric);

  check("range $metric", $range && $range['lower'] == $lower && $range['upper'] == $upper,
        $range ? "got $range[lower] - $range[upper], want $lower - $upper" : 'missing');
}

// 4) A value without a range prints no attributes, the others print both

check('no range prints nothing', $t->attribs('nope') === '');
check('carbs attribs', $t->attribs('carbs') === ' data-lower="0" data-upper="130"', $t->attribs('carbs'));
check('sugar attribs', $t->attribs('sugar') === ' data-lower="0" data-upper="25"', $t->attribs('sugar'));

// 5) The nutrients tab uses the same bounds (rows with an absolute lower / upper)

$rows = [
  'carbs.fibre' => [25.0, 50.0],   // 25 / 40 / 50 g
  'carbs.sugar' => [0.0,  25.0],   // read from the top level of the day entry, like fibre
  'min.NaCl'    => [4.0,  6.0],    // 4 / 5 / 6 g
  'min.Cr'      => [0.03, 0.1],    // 0.03 / 0.065 / 0.1 mg
  'fat.EPA'     => [250.0, 300.0], // 250 / 275 / 300 mg
  'min.Cu'      => [1.0,  1.5],    // DGE range
  'min.Mn'      => [2.0,  5.0],    // DGE range
  'misc.H2O'    => [2000.0, 4000.0],
  'misc.Caf'    => [0.0,  400.0]
];

foreach( $rows as $key => [$lower, $upper] )
{
  $row = $t->nutrient($key);

  check("nutrients tab $key", $row && $row['lower'] == $lower && $row['upper'] == $upper,
        $row ? "got $row[lower] - $row[upper], want $lower - $upper" : 'missing');
}

echo "\n  $pass passed, $fail failed\n";

exit( $fail ? 1 : 0);

?>
