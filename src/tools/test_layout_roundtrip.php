<?php

/*

Verify that a Symfony YAML parse -> dump -> parse round-trip of layout.yml keeps
the structure intact, including the special attrib keys like
"... (color:#ffa500)". Run from src:  php tools/test_layout_roundtrip.php

*/

chdir( dirname(__DIR__));

require_once 'vendor/autoload.php';
require_once 'lib/helper.php';

use Symfony\Component\Yaml\Yaml;

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

$file = 'data/bundles/Default_JaneDoe@example.com-24080101000000/layout.yml';

$original = Yaml::parseFile($file);

// Same arguments as save_layout(): high inline threshold so nested lists stay in
// block style, literal blocks so multi-line help texts stay readable

$dumped   = Yaml::dump($original, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
$reparsed = Yaml::parse($dumped);

// 1) Structure is byte-identical after a round-trip

check('round-trip parses back to identical structure', $original == $reparsed);

// 2) A known special key survives verbatim

$hasColorKey = false;
foreach( $reparsed['Meals'] ?? [] as $key => $_ )
  if( strpos($key, '(color:#ffa500)') !== false )
    $hasColorKey = true;

check('special "(color:#ffa500)" key preserved', $hasColorKey);

// 3) The display parser still extracts the color attrib from the reparsed data

$parsed = parse_layout_attribs('@attribs', ['short', '(i)'], $reparsed['Meals']);
$color  = $parsed['Nuts, seeds and berries']['@attribs']['color'] ?? null;

check('parse_layout_attribs still extracts color', $color === '#ffa500', 'got ' . var_export($color, true));

// 4) No accidental inline flow style (would signal a too-low inline threshold)

check('dump uses block style (no inline "{ ... }")', strpos($dumped, ': {') === false && strpos($dumped, ': [') === false);

// 5) first_entries list content preserved

check('first_entries list intact', ($reparsed['Meals']['(first_entries)']['list'] ?? []) === ($original['Meals']['(first_entries)']['list'] ?? [null]));

// 6) Multi-line help texts stay "|" blocks instead of one line full of "\n"

$multiLine = 0;
array_walk_recursive( $original, function( $v ) use ( &$multiLine ) {
  if( is_string($v) && strpos($v, "\n") !== false )  $multiLine++;
});

check('layout has multi-line values to check', $multiLine > 0, "found $multiLine");
check('multi-line values dumped as literal blocks', strpos($dumped, '\n') === false, 'escaped \n left in the output');

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
