<?php

/*

Standalone tests for FoodYamlWriter: that every field the new-entry modal can
fill reaches the record, in the order of _blank_food.yml, and that the result
parses back to the values that went in.
Run from the `src` directory:  php tools/test_food_yaml_writer.php

*/

chdir( dirname(__DIR__));  // run relative to src/ so require paths resolve

require_once 'vendor/autoload.php';
require_once 'lib/food_import/FoodYamlWriter.php';

use Symfony\Component\Yaml\Yaml;

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;

  if( $ok )
  {
    $pass++;
    echo "  PASS  $name\n";
  }
  else
  {
    $fail++;
    echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n";
  }
}

// A record with every field the modal offers, flags on

$full =
[
  'name'        => 'Test food',
  'type'        => 'Chickpeas',
  'xTimeLog'    => true,
  'productName' => 'Test food, 400 g',
  'vendor'      => 'Rewe',
  'url'         => 'https://example.com/p/1',

  'acceptable'   => 'less',
  'comment'      => 'Kalium',
  'certificates' => ['NutriScore' => 'B', 'oekotest' => '1', 'warentest' => '2', 'vegan' => true, 'bio' => true],
  'details'      => 'Sold in the cooling shelf',
  'ingredients'  => 'Kichererbsen, Wasser, Salz',
  'allergy'      => 'Enthält: Sellerie',
  'mayContain'   => 'Kann Spuren von Nüssen enthalten',
  'origin'       => 'Italy',
  'packaging'    => 'cardboard,alu',

  'careful'             => true,
  'cookingInstructions' => '>= 12 min',

  'price'       => 1.49,
  'weight'      => '400g',
  'usedAmounts' => ['25g', '50g', '100g'],

  'calories'          => 69,
  'nutritionalValues' => ['fat' => 0.7, 'carbs' => 10.2, 'sugar' => 0.9, 'amino' => 4, 'salt' => 0.85],
  'misc'              => ['water' => 80.4],

  'sources' => ['nutriVal' => 'web (information on packaging may differ slightly)', 'price' => 'web'],
  'lastUpd' => '2026-08-24',
];

$yaml   = FoodYamlWriter::toYaml( $full );
$parsed = Yaml::parse( $yaml );

// 1) Every scalar field survives the round trip

foreach( ['type', 'productName', 'vendor', 'url', 'acceptable', 'comment', 'details',
          'ingredients', 'allergy', 'mayContain', 'origin', 'packaging',
          'cookingInstructions', 'weight', 'calories'] as $field )
  check("round trip: $field", ($parsed[$field] ?? null) === $full[$field],
        'got ' . json_encode( $parsed[$field] ?? null));

// 2) The two flags are written as real booleans

check('round trip: xTimeLog is true', ($parsed['xTimeLog'] ?? null) === true, 'got ' . json_encode( $parsed['xTimeLog'] ?? null));
check('round trip: careful is true',  ($parsed['careful']  ?? null) === true, 'got ' . json_encode( $parsed['careful']  ?? null));

// 3) Certificates keep the two test grades next to the other marks

check('round trip: certificates', ($parsed['certificates'] ?? []) == ['NutriScore' => 'B', 'oekotest' => 1, 'warentest' => 2, 'vegan' => true, 'bio' => true],
      'got ' . json_encode( $parsed['certificates'] ?? null));

// 4) misc is its own block, not merged into nutritionalValues

check('round trip: misc.water', ($parsed['misc']['water'] ?? null) == 80.4, 'got ' . json_encode( $parsed['misc'] ?? null));
check('misc kept out of nutritionalValues', ! isset($parsed['nutritionalValues']['water']));

// 5) sources carries both entries

check('round trip: sources', ($parsed['sources'] ?? []) === $full['sources'], 'got ' . json_encode( $parsed['sources'] ?? null));

// 6) Field order follows _blank_food.yml

$order    = [];
$expected = ['type', 'xTimeLog', 'productName', 'vendor', 'url', 'acceptable', 'comment',
             'certificates', 'details', 'ingredients', 'allergy', 'mayContain', 'origin',
             'packaging', 'careful', 'cookingInstructions', 'price', 'weight', 'usedAmounts',
             'calories', 'nutritionalValues', 'misc', 'sources', 'lastUpd', 'lastPriceUpd'];

foreach( explode("\n", $yaml) as $line )
  if( preg_match('/^([a-zA-Z]+):/', $line, $m))
    $order[] = $m[1];

check('field order follows _blank_food.yml', $order === array_values( array_intersect($expected, $order)),
      'got ' . implode(', ', $order));

// 7) Flags that are off, and empty fields, leave no line behind

$minimal = ['name' => 'Plain', 'weight' => '100g', 'calories' => 50, 'careful' => false, 'xTimeLog' => false,
            'nutritionalValues' => ['fat' => 1, 'carbs' => 2, 'sugar' => 0, 'amino' => 3, 'salt' => 0.1]];

$lean = FoodYamlWriter::toYaml( $minimal );

foreach( ['careful', 'xTimeLog', 'type', 'vendor', 'comment', 'details', 'origin', 'misc', 'cookingInstructions'] as $field )
  check("minimal record has no \"$field\" line", ! preg_match("/^$field:/m", $lean));

// 8) A minimal record still parses (no stray blank sections breaking the yaml)

check('minimal record parses', is_array( Yaml::parse( $lean )));

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
