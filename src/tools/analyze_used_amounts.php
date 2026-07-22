<?php

/*

Collect all usedAmounts values across the top-level food yml files and print a
unique, categorized list (precise / fractions / pieces) to help improve the
modalUsedAmountsSelect options.

Run from the `src` directory:  php tools/analyze_used_amounts.php

*/

chdir( dirname(__DIR__));  // run relative to src/

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$dir = 'data/bundles/Default_JaneDoe@example.com-24080101000000/foods';

// Classify a whole usedAmounts set (per food) into one of the three known types.
// Types are not mixed within a food: a "/" means fractions, a g/ml unit means
// precise, otherwise bare integers are pieces

function classifySet( array $amounts ) : string
{
  $joined = implode(' ', $amounts);

  if( strpos($joined, '/') !== false )
    return 'fractions';

  if( preg_match('/\d\s*(g|ml)\b/i', $joined))
    return 'precise';

  return 'pieces';  // bare integers
}

$tokensByType = ['precise' => [], 'fractions' => [], 'pieces' => []];  // unique tokens
$combosByType = ['precise' => [], 'fractions' => [], 'pieces' => []];  // full combinations
$fileCount    = 0;

foreach( scandir($dir) as $file )
{
  // Skip underscore-prefixed files and sub folders; only top-level .yml

  if( $file[0] === '_' || $file[0] === '.' )
    continue;

  $path = "$dir/$file";

  if( is_dir($path) || pathinfo($file, PATHINFO_EXTENSION) !== 'yml' )
    continue;

  $fileCount++;

  $data    = Yaml::parseFile($path);
  $amounts = $data['usedAmounts'] ?? null;

  if( ! is_array($amounts) || $amounts === [])
    continue;

  $amounts = array_values( array_filter( array_map( fn($a) => trim((string) $a), $amounts), fn($a) => $a !== ''));

  if( $amounts === [])
    continue;

  $type  = classifySet($amounts);
  $combo = implode(', ', $amounts);

  $combosByType[$type][$combo] = ($combosByType[$type][$combo] ?? 0) + 1;

  foreach( $amounts as $v )
    $tokensByType[$type][$v] = ($tokensByType[$type][$v] ?? 0) + 1;
}

echo "Scanned $fileCount top-level food files.\n";

foreach( $tokensByType as $type => $tokens )
{
  arsort($tokens);
  arsort($combosByType[$type]);

  echo "\n=== $type ===\n";
  echo "  unique tokens (" . count($tokens) . "): ";
  echo implode('  ', array_map( fn($t, $c) => "$t($c)", array_keys($tokens), array_values($tokens))) . "\n";

  echo "  combinations (" . count($combosByType[$type]) . "):\n";
  foreach( $combosByType[$type] as $combo => $count )
    printf("    %4dx  [%s]\n", $count, $combo);
}

?>
