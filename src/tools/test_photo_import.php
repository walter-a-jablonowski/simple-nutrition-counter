<?php

/*

Standalone tests for the photo import (PhotoImporter, NutritionSanity).

Run from the `src` directory:

  php tools/test_photo_import.php                        offline, no network, no cost
  php tools/test_photo_import.php --models               list model ids that support generateContent
  php tools/test_photo_import.php --live a.jpg b.jpg     real extraction from real pictures

The offline mode replays recorded model answers through the whole mapping, so a
refactor can not silently regress it. Record a new answer with --live.

*/

use Symfony\Component\Yaml\Yaml;

chdir( dirname(__DIR__));  // run relative to src/ so the lib require paths resolve

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/frm/ConfigStatic_240323/config.php';
require_once 'lib/env.php';
require_once 'lib/food_import/FoodImporter.php';
require_once 'lib/food_import/FoodValidator.php';
require_once 'lib/food_import/FoodYamlWriter.php';

config::instance( new SimpleData( Yaml::parse( file_get_contents('config.yml'))));

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


// Model ids that can be used for photoImport.model (the Live model can not)

function list_models()
{
  $key  = env_get('GEMINI_API_KEY');
  $curl = curl_init('https://generativelanguage.googleapis.com/v1beta/models?pageSize=1000');

  curl_setopt_array( $curl, [
    CURLOPT_HTTPHEADER     => ["x-goog-api-key: $key"],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30
  ]);

  $models = json_decode( curl_exec($curl), true)['models'] ?? [];

  curl_close( $curl );

  foreach( $models as $model )
    if( in_array('generateContent', $model['supportedGenerationMethods'] ?? []))
      printf("  %-42s %s\n", str_replace('models/', '', $model['name']), $model['displayName'] ?? '');
}


// The real thing: read pictures of a pack and show what would land in the form

function live_import( array $files )
{
  $images = [];

  foreach( $files as $file )
  {
    if( ! is_file($file))
      exit("File not found: $file\n");

    $images[] = base64_encode( file_get_contents($file));
  }

  $result = FoodImporter::fromImages( $images );

  echo "\n--- food ---\n";
  print_r( $result['food']);

  echo "\n--- warnings ---\n";
  echo $result['warnings'] ? '  ' . implode("\n  ", $result['warnings']) . "\n" : "  none\n";

  echo "\n--- yaml ---\n" . FoodYamlWriter::toYaml( $result['food']);

  $missing = FoodValidator::missingRequired( $result['food']);

  echo "\n--- missing required ---\n  " . ($missing ? implode(', ', $missing) : 'none') . "\n";
}


$mode = $argv[1] ?? '';

if( $mode === '--models')
{
  list_models();
  exit(0);
}

if( $mode === '--live')
{
  live_import( array_slice($argv, 2));
  exit(0);
}


// ---------------------------------------------------------------- offline tests

$good = json_decode( file_get_contents('tools/photo_import/response_good.json'), true);

// 1) A healthy answer maps completely and raises nothing

$result = PhotoImporter::fromAnswer( $good );
$food   = $result['food'];

check('good answer: all 7 nutrients mapped', count( $food['nutritionalValues']) === 7, 'count=' . count( $food['nutritionalValues']));
check('good answer: calories', ($food['calories'] ?? null) == 373, 'calories=' . var_export($food['calories'] ?? null, true));
check('good answer: weight "500 g ℮" -> "500g"', ($food['weight'] ?? '') === '500g', 'weight=' . var_export($food['weight'] ?? null, true));
check('good answer: NutriScore kept', ($food['certificates']['NutriScore'] ?? '') === 'B', json_encode( $food['certificates'] ?? []));
check('good answer: vegan kept', ($food['certificates']['vegan'] ?? null) === true, json_encode( $food['certificates'] ?? []));
check('good answer: "bio": false is dropped', ! array_key_exists('bio', $food['certificates'] ?? []), json_encode( $food['certificates'] ?? []));
check('good answer: null price is dropped', ! array_key_exists('price', $food), json_encode( array_keys($food)));
check('good answer: no warnings', ! $result['warnings'], implode(' | ', $result['warnings']));
check('good answer: passes the required-field contract', FoodValidator::missingRequired( $food ) === [], implode(', ', FoodValidator::missingRequired( $food )));

// 2) A number that arrives as a German string despite the schema

$answer = $good;
$answer['nutritionalValues']['fat'] = '10,2';

$food = PhotoImporter::fromAnswer( $answer )['food'];
check('german number "10,2" -> 10.2', ($food['nutritionalValues']['fat'] ?? null) === 10.2, 'fat=' . var_export($food['nutritionalValues']['fat'] ?? null, true));

// 3) Only kJ printed: kcal is calculated, and the user is told

$answer = $good;
$answer['calories'] = null;

$result = PhotoImporter::fromAnswer( $answer );
check('kJ only: kcal calculated (1560 kJ -> 373)', ($result['food']['calories'] ?? null) == 373, 'calories=' . var_export($result['food']['calories'] ?? null, true));
check('kJ only: warns that kcal was calculated', (bool) preg_grep('/kJ/', $result['warnings']), implode(' | ', $result['warnings']));

// 4) kJ and kcal disagree -> one of the two rows was misread

$answer = $good;
$answer['energyKj'] = 800;

$result = PhotoImporter::fromAnswer( $answer );
check('kJ/kcal mismatch warns', (bool) preg_grep('/do not match/', $result['warnings']), implode(' | ', $result['warnings']));

// 5) Per-portion table: warn, and never scale the values silently

$answer = $good;
$answer['basis'] = 'perPortion';

$result = PhotoImporter::fromAnswer( $answer );
check('perPortion: warns', (bool) preg_grep('/per 100 g/', $result['warnings']), implode(' | ', $result['warnings']));
check('perPortion: values are not scaled', ($result['food']['nutritionalValues']['fat'] ?? null) === 7.1, 'fat=' . var_export($result['food']['nutritionalValues']['fat'] ?? null, true));

// 6) A per-100ml table on a pack sold by weight

$answer = $good;
$answer['basis'] = 'per100ml';

$result = PhotoImporter::fromAnswer( $answer );
check('basis/unit mismatch warns', (bool) preg_grep('/per 100 ml/', $result['warnings']), implode(' | ', $result['warnings']));

// 7) The trace note stays readable even when the model glues it to the ingredients

$answer = $good;
$answer['ingredients'] = 'Vollkorn-HAFERflocken 100%. Kann Spuren von Weizen enthalten.';
$answer['mayContain']  = null;

$food = PhotoImporter::fromAnswer( $answer )['food'];
check('trace note split off the ingredients', ($food['mayContain'] ?? '') === 'Kann Spuren von Weizen enthalten',
      'mayContain=' . var_export($food['mayContain'] ?? null, true));
check('ingredients keep only the ingredients', ($food['ingredients'] ?? '') === 'Vollkorn-HAFERflocken 100%',
      'ingredients=' . var_export($food['ingredients'] ?? null, true));

// 8) Multipack: pieces and total weight

$answer = $good;
$answer['weight'] = '4 x 125 g';
$answer['pieces'] = 4;

$food = PhotoImporter::fromAnswer( $answer )['food'];
check('multipack: pieces kept', ($food['pieces'] ?? null) === 4, 'pieces=' . var_export($food['pieces'] ?? null, true));

// 9) A sparse answer lands in the same required-field contract as the url import

$answer = $good;
$answer['nutritionalValues']['sugar'] = null;
$answer['nutritionalValues']['salt']  = null;

$food    = PhotoImporter::fromAnswer( $answer )['food'];
$missing = FoodValidator::missingRequired( $food );
check('sparse answer: missing fields reported', $missing === ['sugar', 'salt'], 'missing=' . implode(', ', $missing));

// 10) The model's own uncertainty reaches the user

$answer = $good;
$answer['notes'] = 'The salt row was blurred';

$result = PhotoImporter::fromAnswer( $answer );
check('model notes become a warning', (bool) preg_grep('/Could not read: The salt row/', $result['warnings']), implode(' | ', $result['warnings']));

// 11) Plausibility checks

check('sanity: macro sum over 100 g warns',
      (bool) preg_grep('/add up to/', NutritionSanity::warnings(['calories' => 500, 'nutritionalValues' => ['fat' => 50, 'carbs' => 40, 'amino' => 20]])));

check('sanity: nutrients and kcal disagree',
      (bool) preg_grep('/misread/', NutritionSanity::warnings(['calories' => 80, 'nutritionalValues' => ['fat' => 30, 'carbs' => 5, 'amino' => 2]])));

check('sanity: sugar higher than carbs',
      (bool) preg_grep('/Sugar/', NutritionSanity::warnings(['nutritionalValues' => ['sugar' => 10, 'carbs' => 5]])));

check('sanity: saturated fat higher than fat',
      (bool) preg_grep('/Saturated fat/', NutritionSanity::warnings(['nutritionalValues' => ['fat' => 2, 'saturatedFat' => 5]])));

check('sanity: sodium in mg read as salt',
      (bool) preg_grep('/sodium/', NutritionSanity::warnings(['nutritionalValues' => ['salt' => 400]])));

check('sanity: impossible calories',
      (bool) preg_grep('/impossible/', NutritionSanity::warnings(['calories' => 1200])));

// 12) Pictures are checked before anything is sent to google

$tinyPng = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

function throws_with( callable $call, string $expected ) : bool
{
  try {
    $call();
  }
  catch( Exception $e ) {
    return stripos( $e->getMessage(), $expected ) !== false;
  }

  return false;
}

check('junk instead of a picture is rejected',
      throws_with( fn() => PhotoImporter::fromImages(['not base64 at all ###']), 'could not be read'));

check('a non-image payload is rejected',
      throws_with( fn() => PhotoImporter::fromImages([ base64_encode('plain text, not an image')]), 'jpeg, png'));

check('an oversized picture is rejected',
      throws_with( fn() => PhotoImporter::fromImages([ base64_encode( str_repeat('x', 5 * 1024 * 1024))]), 'too large'));

check('more pictures than configured is rejected',
      throws_with( fn() => PhotoImporter::fromImages([ $tinyPng, $tinyPng, $tinyPng, $tinyPng]), 'Too many pictures'));

// 13) The record the import path finally produces. finalize() is private, so the
// test reaches it the same way FoodImporter::fromImages does

$finalize = new ReflectionMethod('FoodImporter', 'finalize');
$finalize->setAccessible( true );

$food  = PhotoImporter::fromAnswer( $good )['food'];
$final = $finalize->invoke( null, $food, 'pack');

check('finalize: source is the packaging', ($final['sources']['nutriVal'] ?? '') === 'pack', json_encode( $final['sources'] ?? []));
check('finalize: lastUpd is today', ($final['lastUpd'] ?? '') === date('Y-m-d'), var_export($final['lastUpd'] ?? null, true));

$sweet = $food;
$sweet['nutritionalValues']['sugar'] = 30;
$sweet = $finalize->invoke( null, $sweet, 'pack');

check('finalize: high sugar presets acceptable', ($sweet['acceptable'] ?? '') === 'less', var_export($sweet['acceptable'] ?? null, true));

$yaml = FoodYamlWriter::toYaml( $final );
check('yaml carries the pack source', strpos($yaml, 'nutriVal: "pack"') !== false, substr($yaml, -120));

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
