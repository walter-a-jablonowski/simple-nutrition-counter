<?php

// Verifies data/food_defaults against /nutrients and against the USDA entries
// the files cite themselves
//
// Two passes:
//
// 1. structure - yml parses, every nutrient key is a substance defined in
//    /nutrients (catches "Fiber" for "Fibre"), lists substances left out
// 2. values    - every value is compared with the fdc ids the file cites in its
//    sources. A value is fine when it matches ANY cited source: files may take
//    single fields from an alternative entry (Butter documents that per field).
//    Ratios near 10, 100 or 1000 are named, that is how the unit bugs looked
//    (minerals are mg except Salt, Potassium, Calcium - see _blank_ai.yml)
//
// Pass 2 also answers what matching values alone cannot: whether the cited entry
// is the right one. Values match their source just as well when the wrong entry
// was taken, so it additionally
//
// - names the cited entry and warns when its description does not read like the
//   type (Olive oil cited an almond entry for a while)
// - holds the file's kcal against the kcal of the foods that use the type, a
//   wide gap means the entry has the wrong state (raw / cooked / roasted)
// - lists the neighbouring fdc ids with --file, the other states of a food sit
//   right next to it in the id range (170184 raw, 170185 dry roasted)
//
// The value pass needs a key for https://fdc.nal.usda.gov, DEMO_KEY works but is
// rate limited to ~30 requests/hour; a free key: fdc.nal.usda.gov/api-key-signup
// Without a usable key it falls back to the portal urls, which have no limit.
//
// CLI usage examples:
//   php verify_food_defaults.php
//   php verify_food_defaults.php --apiKey=abc123
//   php verify_food_defaults.php --file=Lentils --tolerance=0.05
//   php verify_food_defaults.php --skipValues
//
// Browser usage examples:
//   verify_food_defaults.php
//   verify_food_defaults.php?apiKey=abc123&file=Lentils

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

chdir(__DIR__ . '/../..');   // src, the helpers below use paths relative to it

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'models/functions.php';       // expand_food_variants()

$defaultsDir  = 'data/food_defaults';
$nutrientsDir = 'data/bundles/Default_JaneDoe@example.com-24080101000000/nutrients';
$foodsDir     = 'data/bundles/Default_JaneDoe@example.com-24080101000000/foods';

$apiKey     = 'DEMO_KEY';
$tolerance  = 0.02;   // 2%, covers rounding in the files
$only       = '';     // single file (without .yml)
$skipValues = false;

// Options, from the command line or the query string

$options = php_sapi_name() === 'cli'
         ? array_slice( $argv, 1 )
         : array_map( fn($k, $v) => "--$k=$v", array_keys($_GET), $_GET );

foreach( $options as $arg )
{
  if( in_array( $arg, ['--help', '-h']))
  {
    fwrite( STDOUT, "Usage: php verify_food_defaults.php [--apiKey=KEY] [--file=NAME] [--tolerance=0.02] [--skipValues]\n");
    exit(0);
  }
  elseif( strpos( $arg, '--apiKey=') === 0 )
    $apiKey = substr( $arg, 9 );
  elseif( strpos( $arg, '--file=') === 0 )
    $only = basename( substr( $arg, 7 ), '.yml');
  elseif( strpos( $arg, '--tolerance=') === 0 )
    $tolerance = max( 0, min( 0.5, floatval( substr( $arg, 12 ))));
  elseif( $arg === '--skipValues')
    $skipValues = true;
}

if( php_sapi_name() !== 'cli')
  echo '<pre>';


/*@

Field map: yml path => usda nutrient number and the factor from the source unit
to the unit the field uses in the yml (see /nutrients for the units).
Several numbers separated by | are all accepted, the named fatty acid isomers
are what _ai.md maps, but the totals are in use as well.

*/
const FIELDS = [  /*@*/

  'nutritionalValues.fat'             => ['204', 1],
  'nutritionalValues.saturatedFat'    => ['606', 1],
  'nutritionalValues.monoUnsaturated' => ['645', 1],
  'nutritionalValues.polyUnsaturated' => ['646', 1],
  'nutritionalValues.carbs'           => ['205', 1],
  'nutritionalValues.sugar'           => ['269', 1],
  'nutritionalValues.fibre'           => ['291', 1],
  'nutritionalValues.amino'           => ['203', 1],
  'nutritionalValues.salt'            => ['307', 0.001],   // sodium mg -> g

  'fattyAcids.Alpha-linolenic acid'   => ['851|619', 1],
  'fattyAcids.Linoleic acid'          => ['675|618', 1],
  'fattyAcids.Docosahexaenoic acid'   => ['621', 1000],    // g -> mg
  'fattyAcids.Eicosapentaenoic acid'  => ['629', 1000],

  'carbs.Fibre'                       => ['291', 1],

  'aminoAcids.Histidine'              => ['512', 1000],    // g -> mg
  'aminoAcids.Isoleucine'             => ['503', 1000],
  'aminoAcids.Leucine'                => ['504', 1000],
  'aminoAcids.Lysine'                 => ['505', 1000],
  'aminoAcids.Methionine'             => ['506', 1000],
  'aminoAcids.Phenylalanine'          => ['508', 1000],
  'aminoAcids.Threonine'              => ['502', 1000],
  'aminoAcids.Tryptophan'             => ['501', 1000],
  'aminoAcids.Valine'                 => ['510', 1000],

  'vitamins.Vitamin A'                => ['320', 0.001],   // µg RAE -> mg
  'vitamins.Vitamin D'                => ['328', 0.001],
  'vitamins.Vitamin E'                => ['323', 1],
  'vitamins.Vitamin K'                => ['430', 0.001],
  'vitamins.Vitamin C'                => ['401', 1],
  'vitamins.Thiamin B1'               => ['404', 1],
  'vitamins.Riboflavin B2'            => ['405', 1],
  'vitamins.Niacin B3'                => ['406', 1],
  'vitamins.Pantothenic acid B5'      => ['410', 1],
  'vitamins.Vitamin B6'               => ['415', 1],
  'vitamins.Folate B9'                => ['417', 0.001],
  'vitamins.Vitamin B12'              => ['418', 0.001],

  'minerals.Salt'                     => ['307', 0.001],   // sodium mg -> g
  'minerals.Potassium'                => ['306', 0.001],
  'minerals.Calcium'                  => ['301', 0.001],
  'minerals.Phosphorus'               => ['305', 1],       // mg from here on
  'minerals.Magnesium'                => ['304', 1],
  'minerals.Iron'                     => ['303', 1],
  'minerals.Iodine'                   => ['314', 0.001],
  'minerals.Fluoride'                 => ['313', 0.001],
  'minerals.Zinc'                     => ['309', 1],
  'minerals.Selenium'                 => ['317', 0.001],
  'minerals.Copper'                   => ['312', 1],
  'minerals.Manganese'                => ['315', 1],

  'misc.water'                        => ['255', 1]
];

// 958 = Atwater Specific (preferred, see _ai.md), 957 = General, 208 = plain energy

const ENERGY = ['958', '957', '208'];

// yml group => substance file in /nutrients

const GROUPS = [
  'fattyAcids' => 'lipids/fattyAcids',
  'carbs'      => 'carbs',
  'aminoAcids' => 'aminoAcids',
  'vitamins'   => 'vitamins',
  'minerals'   => 'minerals',
  'secondary'  => 'secondary'
];


// Substances defined in /nutrients

$substances = [];

foreach( GROUPS as $group => $file )
{
  $def = Yaml::parse( file_get_contents("$nutrientsDir/$file.yml"));
  $substances[$group] = array_keys( $def['substances'] ?? []);
}

// Food defaults, parsed once - files that do not parse are reported and skipped

$defaults = $parseErrors = [];

foreach( scandir( $defaultsDir ) as $file )
{
  if( $file[0] === '_' || pathinfo( $file, PATHINFO_EXTENSION) !== 'yml')
    continue;

  $name = pathinfo( $file, PATHINFO_FILENAME);

  if( $only !== '' && $name !== $only )
    continue;

  $text = file_get_contents("$defaultsDir/$file");

  try {
    $defaults[$name] = Yaml::parse( $text );
  }
  catch( ParseException $e ) {
    $parseErrors[$name] = $e->getMessage();
    continue;
  }

  // the fdc ids the file cites, in the order they appear

  preg_match_all('#food-details/(\d+)#', $text, $matches );
  $defaults[$name]['xCitedIds'] = array_values( array_unique( $matches[1] ));
}

// The foods that use a type, for the kcal comparison in pass 2

$foodsByType = [];

foreach( scandir( $foodsDir ) as $file )
{
  if( in_array( $file, ['.', '..']) || $file[0] === '_' || ( pathinfo( $file, PATHINFO_EXTENSION) !== 'yml' && ! is_dir("$foodsDir/$file")))
    continue;

  $foodName = is_dir("$foodsDir/$file")  ?  $file  :  pathinfo( $file, PATHINFO_FILENAME);
  $path     = is_file("$foodsDir/$file") ? "$foodsDir/$file" : "$foodsDir/$file/-this.yml";
  $food     = Yaml::parse( file_get_contents( $path ));

  if( ! is_array( $food ))
    continue;

  foreach( expand_food_variants( $foodName, $food ) as $name => $data )
  {
    $type = trim((string)( $data['type'] ?? ''));

    if( $type !== '' && ! empty( $data['calories'] ))
      $foodsByType[$type][ $name ] = $data['calories'];
  }
}

// Pass 1: structure

echo "== structure\n\n";

foreach( $parseErrors as $name => $message )
  echo "$name: DOES NOT PARSE - $message\n";

foreach( $defaults as $name => $data )
{
  $unknown = $left = [];

  foreach( GROUPS as $group => $ignore )
  {
    if( ! isset( $data[$group] ) || ! is_array( $data[$group] ))
      continue;

    foreach( array_diff( array_keys( $data[$group] ), $substances[$group] ) as $key )
      $unknown[] = "$group.$key";

    foreach( array_diff( $substances[$group], array_keys( $data[$group] )) as $key )
      $left[] = "$group.$key";
  }

  if( ! $unknown && ! $left )
    echo str_pad( $name, 20 ) . "ok\n";
  else
  {
    echo str_pad( $name, 20 ) . ( $data['xCitedIds'] ? 'cites ' . implode(', ', $data['xCitedIds']) : 'cites no fdc id') . "\n";

    if( $unknown )
      echo "  unknown keys (no such substance): " . implode(', ', $unknown) . "\n";
    if( $left )
      echo "  not filled: " . implode(', ', $left) . "\n";
  }
}

if( $skipValues )
{
  if( php_sapi_name() !== 'cli')  echo '</pre>';
  exit(0);
}

// Pass 2: values against the cited sources

echo "\n== values (tolerance " . round( $tolerance * 100 ) . "%)\n\n";

$ids = [];

foreach( $defaults as $data )
  $ids = array_merge( $ids, $data['xCitedIds'] ?? []);

$ids = array_values( array_unique( $ids ));

// fdc id => nutrient number => amount
//
// The api takes all ids in one request but needs a key and is rate limited.
// The portal endpoint the fdc website itself uses needs no key and has no
// limit, but serves one food per request and names the value differently.

$sources = $descriptions = [];

$response = http_get('https://api.nal.usda.gov/fdc/v1/foods?api_key=' . urlencode($apiKey) . '&fdcIds=' . implode(',', $ids));

if( $response['status'] === 200 )
{
  foreach( json_decode( $response['body'], true ) ?? [] as $food )
  {
    $sources[ $food['fdcId'] ]      = collect_nutrients( $food, 'amount');
    $descriptions[ $food['fdcId'] ] = $food['description'] ?? '';
  }
}
else
{
  echo 'api not usable (http ' . $response['status'] . ', rate limited without an own --apiKey), using the portal endpoint' . "\n\n";

  foreach( $ids as $id )
  {
    $food = portal_food( $id );

    if( $food )
    {
      $sources[$id]      = collect_nutrients( $food, 'value');
      $descriptions[$id] = $food['description'] ?? '';
    }
  }
}

if( ! $sources )
{
  echo "no source data could be fetched\n";

  if( php_sapi_name() !== 'cli')  echo '</pre>';
  exit(1);
}

foreach( array_diff( $ids, array_keys( $sources )) as $unknownId )
  echo "note: fdc id $unknownId was not returned (retired id?)\n";

foreach( $defaults as $name => $data )
{
  $have = array_intersect( $data['xCitedIds'] ?? [], array_keys( $sources ));

  if( ! $have )
  {
    echo str_pad( $name, 20 ) . "no cited source available, not checked\n";
    continue;
  }

  // Every value the file has, against every source it cites

  $issues = [];

  foreach( FIELDS as $path => [$numbers, $factor] )
  {
    [$group, $key] = explode('.', $path, 2);

    if( ! isset( $data[$group][$key] ) || ! is_numeric( $data[$group][$key] ))
      continue;

    $value    = (float)$data[$group][$key];
    $expected = [];

    foreach( $have as $id )
      foreach( explode('|', $numbers) as $number )
        if( isset( $sources[$id][$number] ))
          $expected[] = [ $sources[$id][$number] * $factor, $id ];

    if( $expected )
      $issues = array_merge( $issues, compare_value( $path, $value, $expected, $tolerance ));
  }

  // Calories: all three energy variants are accepted, the files note which one they took

  if( isset( $data['calories'] ) && is_numeric( $data['calories'] ))
  {
    $expected = [];

    foreach( $have as $id )
      foreach( ENERGY as $number )
        if( isset( $sources[$id][$number] ))
          $expected[] = [ $sources[$id][$number], "$id#$number" ];

    if( $expected )
      $issues = array_merge( $issues, compare_value('calories', (float)$data['calories'], $expected, $tolerance ));
  }

  echo str_pad( $name, 20 ) . ( $issues ? count($issues) . ' to check' : 'ok') . ' (' . implode(', ', $have) . ")\n";

  foreach( $issues as $issue )
    echo "  $issue\n";

  // Is it the right entry? Matching values say nothing about that, the checks
  // below do: the description, the kcal of the foods, and the other states of
  // the food, which sit next to the entry in the id range

  foreach( $have as $id )
  {
    $description = $descriptions[$id] ?? '';

    if( $description !== '' && ! description_matches( $name, $description ))
      echo "  cited $id is \"$description\" - does not read like $name\n";
  }

  if( isset( $data['calories'] ) && is_numeric( $data['calories'] ) && ! empty( $foodsByType[$name] ))
  {
    $kcal = (float)$data['calories'];
    $wide = false;
    $shown = [];

    foreach( $foodsByType[$name] as $foodName => $foodKcal )
    {
      $off     = ( $foodKcal - $kcal ) / $kcal;
      $shown[] = "$foodName $foodKcal (" . sprintf('%+d', round( $off * 100 )) . '%)';

      if( abs( $off ) > 0.15 )
        $wide = true;
    }

    echo "  kcal $data[calories] vs foods: " . implode(', ', $shown) . ( $wide ? '  <- wide gap, check the state of the entry' : '') . "\n";
  }

  if( $only === '')
    continue;

  // Single file: the neighbouring ids, that is where the other states are

  foreach( $have as $id )
  {
    for( $offset = -2; $offset <= 2; $offset++ )
    {
      if( $offset === 0 || ! ( $food = portal_food( $id + $offset )))
        continue;

      $values = collect_nutrients( $food, 'value');
      $kcal   = '?';

      foreach( ENERGY as $number )        // Foundation entries carry only the Atwater variants
        if( isset( $values[$number] ))
          { $kcal = round( $values[$number] );  break; }

      echo '  near ' . ( $id + $offset ) . ': ' . str_pad( "$kcal kcal", 10 )
         . str_pad( count($values) . ' values', 12 ) . ( $food['description'] ?? '') . "\n";
    }
  }
}

if( php_sapi_name() !== 'cli')
  echo '</pre>';


/*@

http_get()

*/
function http_get( $url )  /*@*/
{
  $curl = curl_init( $url );

  curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $curl, CURLOPT_TIMEOUT, 60 );
  curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );

  $body   = curl_exec( $curl );
  $status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

  curl_close( $curl );

  return ['status' => $status, 'body' => (string)$body];
}


/*@

portal_food()

One entry from the endpoint the fdc website uses, no key and no rate limit.
Returns null for ids that do not exist, which is normal when walking neighbours.

*/
function portal_food( $id )  /*@*/
{
  $response = http_get("https://fdc.nal.usda.gov/portal-data/external/$id");

  if( $response['status'] !== 200 )
    return null;

  $food = json_decode( $response['body'], true );

  return ! empty( $food['description'] )  ?  $food  :  null;
}


/*@

description_matches()

Does the cited entry read like the food the file is for? The descriptions are
worded the other way round ("Nuts, walnuts, english" for Walnuts), so one word
in common is enough. Plurals and small typos still count - the file name Poato
must not be flagged against "Potatoes, gold, without skin, raw".

*/
function description_matches( $type, $description )  /*@*/
{
  $words = fn( $text ) => array_filter( preg_split('/[^a-z]+/', mb_strtolower($text)), fn($w) => strlen($w) > 2 );

  foreach( $words( $type ) as $word )
  {
    $word = rtrim( $word, 's');

    foreach( $words( $description ) as $other )
    {
      $other = rtrim( $other, 's');

      if( strpos( $other, $word ) === 0 || strpos( $word, $other ) === 0 )
        return true;

      if( strlen( $word ) > 4 && levenshtein( $word, $other ) <= 2 )
        return true;
    }
  }

  return false;
}


/*@

collect_nutrients()

nutrient number => amount. The api calls the value "amount", the portal
endpoint "value", everything else about the two shapes is the same.
First entry per number wins, that is the one the fdc pages show on top.

*/
function collect_nutrients( $food, $valueKey )  /*@*/
{
  $values = [];

  foreach( $food['foodNutrients'] ?? [] as $entry )
  {
    $number = (string)( $entry['nutrient']['number'] ?? '');
    $amount = $entry[$valueKey] ?? null;

    if( $amount !== null && $number !== '' && ! isset( $values[$number] ))
      $values[$number] = $amount;
  }

  return $values;
}


/*@

compare_value()

Returns one line per value that matches none of its sources, with the factor
named when the difference is a plain unit slip (10, 100, 1000).

*/
function compare_value( $path, $value, $expected, $tolerance )  /*@*/
{
  foreach( $expected as [$amount, $id] )
    if( abs( $value - $amount ) <= max( $tolerance * abs($amount), 1e-9 ))
      return [];

  $shown = [];

  foreach( $expected as [$amount, $id] )
    $shown[] = round( $amount, 6 ) . " [$id]";

  // the closest source decides how the difference is named

  $closest = $expected[0][0];

  foreach( $expected as [$amount, $id] )
    if( abs( $amount - $value ) < abs( $closest - $value ))
      $closest = $amount;

  $hint = '';

  if( $value != 0 && $closest != 0 )
  {
    $ratio = $closest / $value;

    foreach([1000, 100, 10] as $factor )
    {
      if( abs( $ratio - $factor ) < $factor * 0.05 )
        { $hint = "  <- yml is {$factor}x too small";  break; }

      if( abs( $ratio - 1 / $factor ) < 0.05 / $factor )
        { $hint = "  <- yml is {$factor}x too large";  break; }
    }
  }

  return [ str_pad( $path, 34 ) . 'yml ' . str_pad( (string)$value, 12 ) . 'source ' . implode(', ', $shown) . $hint ];
}
