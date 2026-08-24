<?php

/*

Standalone test for the food defaults merge (CombinedModel + LayoutView).

Run from the `src` directory:  php tools/test_food_defaults_merge.php

A food links to its reference panel with `type: Broccoli`, which merges
`food_defaults/Broccoli.yml` under the food's own values. This test walks the real
bundle and checks that every nutrient group of a default arrives in the food model
and in the amount buttons of the grid.

The group names in NUTRIENT_GROUPS are paths under /nutrients, and fatty acids live
in a subfolder there. The food files hold every group under its plain name, so the
folder must not travel into the food record - that is what broke fatty acids and
what the last two checks watch.

*/

use Symfony\Component\Yaml\Yaml;

chdir( dirname(__DIR__));  // run relative to src/ so the require paths resolve

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/frm/ConfigStatic_240323/config.php';
require_once 'lib/settings.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

// Stub of the app's User: the models only need the id to find the bundle

class User
{
  public string $id;
  private static ?User $instance = null;

  public static function current( ?string $key = null )
  {
    self::$instance ??= new User();
    self::$instance->id = config::get('defaultUser');

    return $key === null ? self::$instance : null;
  }
}

config::instance( new SimpleData( Yaml::parse( file_get_contents('config.yml'))));

require_once 'models/CombinedModel.php';
require_once 'models/LayoutView.php';

// Minimal host for the two traits, built the way AppController::render() builds them

class MergeTest
{
  use CombinedModel;
  use LayoutView;

  const NUTRIENT_GROUPS = ['lipids/fattyAcids', 'carbs', 'aminoAcids', 'vitamins', 'minerals', 'secondary', 'misc'];

  protected SimpleData $nutrientsModel;

  public function __construct()
  {
    $user   = User::current();
    $bundle = "data/bundles/Default_$user->id";

    $this->nutrientsModel = new SimpleData();

    foreach( self::NUTRIENT_GROUPS as $groupName )
      $this->nutrientsModel->set( $groupName, Yaml::parse( file_get_contents("$bundle/nutrients/$groupName.yml")));

    settings::instance( new SimpleData( array_merge(
      config::get('defaultSettings') ?: [],
      Yaml::parse( file_get_contents("data/users/$user->id/settings.yml")) ?: [])));

    $this->makeCombinedModel();
    $this->makeLayoutView();
  }

  public function food( string $name )   { return $this->combinedModel->get( $name ); }
  public function amounts( string $name ){ return $this->layoutView->get( $name ); }
  public function allFoods()             { return $this->combinedModel->all(); }
}

$t = new MergeTest();

/* The foods to look at: everything that links to a default which carries the group.
   Data driven on purpose - a default gained or lost is no reason for a red test */

function foods_with( MergeTest $t, string $defaultKey ) : array
{
  $found = [];

  foreach( $t->allFoods() as $name => $data )
  {
    $type = $data['type'] ?? null;

    if( ! $type || ! is_file("data/food_defaults/$type.yml"))
      continue;

    $default = Yaml::parse( file_get_contents("data/food_defaults/$type.yml"));

    if( ! empty( $default[$defaultKey]))
      $found[$name] = $type;
  }

  return $found;
}

// 1) Vitamins: the group that has always worked, here as the regression guard

$withVitamins = foods_with( $t, 'vitamins');

check('foods link to a default with vitamins', count($withVitamins) > 0, count($withVitamins) . ' found');

$missing = [];

foreach( $withVitamins as $name => $type )
  if( empty( $t->food("$name.vitamins")))
    $missing[] = "$name ($type)";

check('vitamins reach the food model', ! $missing, count($missing) . ' without: ' . implode(', ', array_slice($missing, 0, 3)));

// 2) Fatty acids: same path, and the group the folder in the group name used to break

$withFat = foods_with( $t, 'fattyAcids');

check('foods link to a default with fatty acids', count($withFat) > 0, count($withFat) . ' found');

$missing = [];

foreach( $withFat as $name => $type )
  if( empty( $t->food("$name.fattyAcids")))
    $missing[] = "$name ($type)";

check('fatty acids reach the food model', ! $missing, count($missing) . ' without: ' . implode(', ', array_slice($missing, 0, 3)));

// 3) ... and arrive on the amount buttons, where the day entry reads them from

$missing = [];

foreach( $withFat as $name => $type )
{
  $amounts = $t->amounts( $name );
  $first   = is_array($amounts) ? reset($amounts) : null;

  if( empty( $first['fat']))
    $missing[] = "$name ($type)";
}

check('fatty acids reach the amount buttons', ! $missing, count($missing) . ' without: ' . implode(', ', array_slice($missing, 0, 3)));

// 4) The substances are keyed by the short names of the nutrients model, not by their
//    long names - a food whose values never pass the model filter would look filled

$sample = array_key_first( $withFat );

if( $sample )
{
  $amounts = $t->amounts( $sample );
  $first   = is_array($amounts) ? reset($amounts) : [];
  $shorts  = array_keys( $first['fat'] ?? []);

  check("fatty acid shorts of '$sample'", (bool) array_intersect( $shorts, ['ALA', 'LA', 'EPA', 'DHA']),
        'got: ' . (implode(', ', $shorts) ?: 'nothing'));
}

echo "\n  $pass passed, $fail failed\n";

exit( $fail ? 1 : 0);

?>
