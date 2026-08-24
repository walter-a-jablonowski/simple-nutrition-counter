<?php

/*

Standalone tests for the advisor's intake report (see lib/advisor/NutrientReport.php).

Run from the `src` directory:  php tools/test_nutrient_report.php

Offline, no network, no cost. The report runs against fixture day files of a temporary
user and a hand made bounds table, so it depends on neither the sample data nor the
nutrients model.

What it watches:

- daily averages, over days the user logged nothing on as well
- the status against the bounds the nutrients tab prints
- coverage per group, the number that decides whether a zero is a deficit or a data gap
- fibre and sugar, which sit at the top level of an entry instead of in a group

*/

use Symfony\Component\Yaml\Yaml;

chdir( dirname(__DIR__));  // run relative to src/ so the require paths resolve

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/advisor/NutrientReport.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

// The bounds table in the shape NutrientsView builds it: group short => nutrient short

$view = new SimpleData([
  'vit' => [
    'C' => ['name' => 'Vitamin C', 'displayName' => null, 'unit' => 'mg', 'lower' =>  90, 'ideal' => 100, 'upper' => 200],
    'D' => ['name' => 'Vitamin D', 'displayName' => null, 'unit' => 'mg', 'lower' => 0.018, 'ideal' => 0.02, 'upper' => 0.05]
  ],
  'min' => [
    'Fe'   => ['name' => 'Iron', 'displayName' => null, 'unit' => 'mg', 'lower' => 9,   'ideal' => 10,  'upper' => 20],
    'NaCl' => ['name' => 'Salt', 'displayName' => null, 'unit' => 'g',  'lower' => 4,   'ideal' => 5,   'upper' => 6]
  ],
  'carbs' => [
    'fibre' => ['name' => 'Fibre', 'displayName' => null, 'unit' => 'g', 'lower' => 40, 'ideal' => 45, 'upper' => 50],
    'sugar' => ['name' => 'Sugar', 'displayName' => null, 'unit' => 'g', 'lower' =>  0, 'ideal' => 25, 'upper' => 50]
  ]
]);

$captions = ['vit' => 'Vitamins', 'min' => 'Minerals', 'carbs' => 'Carbs'];

// Fixture days. Anchor 2026-01-08 with a 4 day range covers 01-05 .. 01-08:
// two days with entries, one day with a file but nothing eatable, one missing day

$user = '_test_nutrient_report';
$dir  = "data/users/$user/days";

if( ! is_dir($dir))
  mkdir( $dir, 0777, true);

// A food with a full panel (200 kcal) and one without (300 kcal), so coverage is 40 %

$withPanel = '{amount: {label: "1", weight: 100}, fibre: 10, sugar: 4, '
           . 'fat: {}, amino: {}, vit: {C: 20, D: 0.001}, min: {Fe: 2, NaCl: 1}, sec: {}, misc: {H2O: 80}}';

$noPanel   = '{amount: {label: "1", weight: 100}, fibre: 2, sugar: 30, '
           . 'fat: {}, amino: {}, vit: {}, min: {}, sec: {}, misc: {}}';

// An old style entry: written before sugar and misc existed, so it has neither key

$oldStyle  = '{amount: {label: "1", weight: 100}, fibre: 1, '
           . 'fat: {}, amino: {}, vit: {}, min: {}, sec: {}, misc: {}}';

file_put_contents("$dir/2026-01-05.tsv",
  "unprecise: false\n\n" .   // header block, must not reach the entries
  "08:00:00  F  Panel   200  1  20  5  1    0.50  $withPanel\n" .
  "12:00:00  F  Plain   300  2  40  3  0.5  0.80  $noPanel\n");

file_put_contents("$dir/2026-01-06.tsv",
  "08:00:00  F  Panel   200  1  20  5  1    0.50  $withPanel\n" .
  "12:00:00  F  Plain   300  2  40  3  0.5  0.80  $noPanel\n" .
  "20:00:00  W  Sport   0    0  0   0  0    0     {amount: {label: \"1\", weight: 0}, vit: {C: 999}}\n");

file_put_contents("$dir/2026-01-07.tsv",
  "20:00:00  W  Sport   0  0  0  0  0  0  {amount: {label: \"1\", weight: 0}, vit: {C: 999}}\n");

// 2026-01-08 has no file at all - it still counts as a day of the range

$report = (new NutrientReport( $view, $captions, $user, 40))->forRange('4days', '2026-01-08');

$rows = [];
foreach( $report['nutrients'] as $row )
  $rows["$row[group].$row[short]"] = $row;

// 1) The days of the range

check('range length',   $report['days'] === 4,         (string) $report['days']);
check('days with data', $report['daysWithData'] === 2, (string) $report['daysWithData']);

// 2) Daily averages divide by the days of the range, not by the days with entries.
//    Vitamin C: 20 twice over 4 days

check('avg over the whole range', $rows['vit.C']['perDay'] === 10.0, (string) $rows['vit.C']['perDay']);
check('non food type ignored',    $rows['vit.C']['perDay'] < 900);

// 3) Fibre and sugar come off the top level of the entry.
//    Fibre: (10 + 2) twice over 4 days = 6, sugar: (4 + 30) twice over 4 days = 17

check('top level fibre', $rows['carbs.fibre']['perDay'] === 6.0,  (string) $rows['carbs.fibre']['perDay']);
check('top level sugar', $rows['carbs.sugar']['perDay'] === 17.0, (string) $rows['carbs.sugar']['perDay']);

// 4) Status against the bounds, and the distance to the target

check('below',         $rows['vit.C']['status'] === 'below', $rows['vit.C']['status']);
check('gap to ideal',  $rows['vit.C']['gap'] === 90.0,       var_export( $rows['vit.C']['gap'], true));
check('no over below', $rows['vit.C']['over'] === null);

check('ok inside the bounds', $rows['carbs.sugar']['status'] === 'ok', $rows['carbs.sugar']['status']);
check('no gap when ok',       $rows['carbs.sugar']['gap'] === null);

// Salt: 1 g twice over 4 days = 0.5, below its lower bound of 4

check('salt below', $rows['min.NaCl']['status'] === 'below', $rows['min.NaCl']['status']);

// 5) Coverage: 200 of 500 kcal a day carry a panel, and every entry carries fibre

check('vit coverage',   $report['coverage']['vit']  === 40,  (string) $report['coverage']['vit']);
check('min coverage',   $report['coverage']['min']  === 40,  (string) $report['coverage']['min']);
check('misc coverage',  $report['coverage']['misc'] === 40,  (string) $report['coverage']['misc']);
check('empty group has no coverage', $report['coverage']['fat'] === 0, (string) $report['coverage']['fat']);

// Fibre and sugar carry their own coverage, every entry here has both

check('fibre coverage', $report['coverage']['carbs.fibre'] === 100, (string) $report['coverage']['carbs.fibre']);
check('sugar coverage', $report['coverage']['carbs.sugar'] === 100, (string) $report['coverage']['carbs.sugar']);
check('row carries its coverage', $rows['carbs.fibre']['coverage'] === 100 && $rows['vit.C']['coverage'] === 40);

// 6) The threshold decides whether a zero may be read as a deficit

check('measurable at the threshold', $rows['vit.C']['measurable'] === true);
check('carbs measurable',            $rows['carbs.fibre']['measurable'] === true);

$strict = (new NutrientReport( $view, $captions, $user, 60))->forRange('4days', '2026-01-08');
$vitC   = null;

foreach( $strict['nutrients'] as $row )
  if( $row['short'] === 'C' )  $vitC = $row;

check('unmeasurable below the threshold', $vitC['measurable'] === false);
check('the value is still reported',      $vitC['perDay'] === 10.0, (string) $vitC['perDay']);

// 7) Macros come off the tsv columns, water out of the misc group

check('macro calories', $report['macros']['calories'] === 250.0, (string) $report['macros']['calories']);
check('macro salt',     $report['macros']['salt']     === 0.75,  (string) $report['macros']['salt']);
check('macro price',    $report['macros']['price']    === 0.65,  (string) $report['macros']['price']);
check('macro water',    $report['macros']['water']    === 40.0,  (string) $report['macros']['water']);
check('macro fibre',    $report['macros']['fibre']    === 6.0,   (string) $report['macros']['fibre']);

// 8) An entry written before sugar existed carries fibre but no sugar key. That must
//    lower sugar's coverage only - fibre was measured for it, sugar was not

file_put_contents("$dir/2026-01-06.tsv",
  "08:00:00  F  Panel  200  1  20  5  1    0.50  $withPanel\n" .
  "12:00:00  F  Old    300  2  40  3  0.5  0.80  $oldStyle\n");

$mixed = (new NutrientReport( $view, $captions, $user, 40))->forRange('4days', '2026-01-08');

check('old entry keeps fibre coverage', $mixed['coverage']['carbs.fibre'] === 100,
      (string) $mixed['coverage']['carbs.fibre']);

check('old entry lowers sugar coverage', $mixed['coverage']['carbs.sugar'] === 70,
      (string) $mixed['coverage']['carbs.sugar']);   // 350 of 500 kcal a day carry sugar

// 9) An unknown range is reported, not silently treated as a day

check('unknown range', (new NutrientReport( $view, $captions, $user, 40))->forRange('lastYear', '2026-01-08') === null);

// 10) A range without a single logged day gives zeroes, not a crash

$empty = (new NutrientReport( $view, $captions, $user, 40))->forRange('7days', '2020-01-08');

check('empty range has days',     $empty['days'] === 7);
check('empty range has no data',  $empty['daysWithData'] === 0);
check('empty range zeroes',       $empty['nutrients'][0]['perDay'] === 0.0);
check('empty range no coverage',  $empty['coverage']['vit'] === 0);
check('empty range unmeasurable', $empty['nutrients'][0]['measurable'] === false);

// Clean up the fixtures

foreach( scandir($dir) as $file )
  if( ! in_array( $file, ['.', '..']))  unlink("$dir/$file");

rmdir( $dir);
rmdir( "data/users/$user");

echo "\n  $pass passed, $fail failed\n";

exit( $fail ? 1 : 0);

?>
