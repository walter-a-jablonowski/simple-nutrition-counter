<?php

/*

Standalone tests for the amount scaling of the food grid (see LayoutView).
Run from the `src` directory:  php tools/test_layout_view.php

*/

use Symfony\Component\Yaml\Yaml;

chdir( dirname(__DIR__));

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/settings.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

// Stub of the app's User: makeLayoutView() only needs the instance

class User
{
  public static function current( ?string $key = null )
  {
    return null;
  }
}

require_once 'models/LayoutView.php';

// Minimal host for the trait, filled with the real nutrients data and two test foods

class LayoutViewTest
{
  use LayoutView;

  const NUTRIENT_GROUPS = ['lipids/fattyAcids', 'carbs', 'aminoAcids', 'vitamins', 'minerals', 'secondary', 'misc'];

  protected SimpleData $nutrientsModel;
  protected SimpleData $combinedModel;

  public function __construct()
  {
    $bundle = 'data/bundles/Default_JaneDoe@example.com-24080101000000';

    $this->nutrientsModel = new SimpleData();

    foreach( self::NUTRIENT_GROUPS as $groupName )
      $this->nutrientsModel->set( $groupName, Yaml::parse( file_get_contents("$bundle/nutrients/$groupName.yml")));

    $this->combinedModel = new SimpleData( self::foods());

    settings::instance( new SimpleData());

    $this->makeLayoutView();
  }

  // Mineral water (small mineral amounts, precise amount) and a food with a pack amount

  private static function foods() : array
  {
    return
    [
      'Mineral water' =>
      [
        'category'          => 'F',
        'weight'            => '330ml',
        'usedAmounts'       => ['330ml'],
        'calories'          => 0,
        'nutritionalValues' => ['fat' => 0, 'carbs' => 0, 'sugar' => 0, 'amino' => 0, 'salt' => 0],
        'minerals'          => [
          'Salt'      => 0.00132,  // g, sodium 1.32 mg per 100 ml
          'Potassium' => 0.0001,   // g
          'Calcium'   => 0.00676,  // g
          'Magnesium' => 3.29,     // mg
          'Fluoride'  => 0.016,    // mg
          'Ghost'     => 1         // not in nutrients/minerals.yml
        ],
        'misc'              => ['water' => 100]
      ],
      'Test bar' =>
      [
        'category'          => 'F',
        'weight'            => '100g',
        'usedAmounts'       => ['1/3'],
        'calories'          => 400,
        'nutritionalValues' => ['fat' => 12.345, 'carbs' => 30, 'sugar' => 10, 'amino' => 8, 'salt' => 0.5],
        'minerals'          => ['Calcium' => 0.85]  // g
      ]
    ];
  }

  public function amount( string $key )
  {
    return $this->layoutView->get( $key );
  }
}

$t = new LayoutViewTest();

// 1) Precise amount: the weight is the amount itself, water is g (= ml)

check('precise weight',   $t->amount('Mineral water.330ml.weight') === 330.0,   var_export( $t->amount('Mineral water.330ml.weight'), true));
check('water 330 ml',     $t->amount('Mineral water.330ml.misc.H2O') === 330.0, var_export( $t->amount('Mineral water.330ml.misc.H2O'), true));

// 2) Small mineral amounts survive the scaling: 5 decimals for the nutrient groups,
//    the unit does not matter (grams used to be rounded to 1 decimal, so these were 0)

$minerals = [
  'NaCl' => 0.00436,  // 0.00132 g * 3.3
  'K'    => 0.00033,  // 0.0001  g * 3.3
  'Ca'   => 0.02231,  // 0.00676 g * 3.3
  'Mg'   => 10.857,   // 3.29 mg * 3.3
  'F'    => 0.0528    // 0.016 mg * 3.3
];

foreach( $minerals as $short => $expected )
{
  $got = $t->amount("Mineral water.330ml.min.$short");

  check("mineral $short", $got === $expected, var_export( $got, true) . " want $expected");
}

check('unknown substance is skipped', $t->amount('Mineral water.330ml.min.Ghost') === null);

// 3) The nutritional values stay at 1 decimal, they are printed in the day list

check('pack weight',      $t->amount('Test bar.1/3.weight')             === 33.3);
check('nutriVal fat 1 decimal', $t->amount('Test bar.1/3.nutriVal.fat') === 4.1,  var_export( $t->amount('Test bar.1/3.nutriVal.fat'), true));
check('nutriVal salt 1 decimal', $t->amount('Test bar.1/3.nutriVal.salt') === 0.2, var_export( $t->amount('Test bar.1/3.nutriVal.salt'), true));
check('calories 1 decimal', $t->amount('Test bar.1/3.calories')         === 133.3, var_export( $t->amount('Test bar.1/3.calories'), true));

// 4) A mineral of a normal food keeps its precision as well (0.85 g * 33.333 / 100)

check('mineral of a pack amount', $t->amount('Test bar.1/3.min.Ca') === 0.28333, var_export( $t->amount('Test bar.1/3.min.Ca'), true));

echo "\n  $pass passed, $fail failed\n";

exit( $fail ? 1 : 0);

?>
