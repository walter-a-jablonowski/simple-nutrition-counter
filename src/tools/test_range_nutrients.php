<?php

/*

Standalone tests for the time range of the nutrients tab (see ajax/get_range_nutrients.php):
which days a range covers and how the day files are aggregated into daily averages.
Run from the `src` directory:  php tools/test_range_nutrients.php

The aggregation runs against fixture day files of a temporary user, so it does not
depend on the sample data

*/

use Symfony\Component\Yaml\Yaml;

chdir( dirname(__DIR__));

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/frm/ConfigStatic_240323/config.php';
require_once 'ajax/get_range_nutrients.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

// Minimal host for the trait, the constants live in AppController

class RangeNutrientsTest
{
  use GetRangeNutrientsAjaxController;

  const DAY_HEADERS = ['time', 'type', 'food', 'calories', 'fat', 'carbs', 'amino', 'salt', 'price', 'nutrients'];
  const FOOD_TYPES  = ['F', 'FE', 'S', 'M'];

  public function dates( string $range, string $anchor ) : ?array
  {
    return $this->rangeDates( $range, $anchor );
  }
}

$t = new RangeNutrientsTest();

// 1) Rolling windows: N days ending at the day the app shows

$dates = $t->dates('7days', '2026-01-20');

check('7days count',  count($dates) === 7, count($dates) . ' dates');
check('7days first',  $dates[0] === '2026-01-14', $dates[0]);
check('7days last',   end($dates) === '2026-01-20', end($dates));
check('30days count', count( $t->dates('30days', '2026-01-20')) === 30);
check('90days count', count( $t->dates('90days', '2026-01-20')) === 90);

// 2) The running day is left out, it is only logged up to the current time

$today     = new DateTimeImmutable('today');
$yesterday = $today->modify('-1 day');
$dates     = $t->dates('7days', $today->format('Y-m-d'));

check('today: 7 days stay 7', count($dates) === 7, count($dates) . ' dates');
check('today: ends yesterday', end($dates) === $yesterday->format('Y-m-d'), end($dates));

// 3) Calendar periods, weeks start on monday

$dates = $t->dates('thisWeek', '2026-01-21');   // a wednesday in the past

check('thisWeek count', count($dates) === 3, count($dates) . ' dates');
check('thisWeek first', $dates[0] === '2026-01-19', $dates[0]);
check('thisWeek last',  end($dates) === '2026-01-21', end($dates));

$dates = $t->dates('lastWeek', '2026-01-21');

check('lastWeek count', count($dates) === 7, count($dates) . ' dates');
check('lastWeek first', $dates[0] === '2026-01-12', $dates[0]);
check('lastWeek last',  end($dates) === '2026-01-18', end($dates));

$dates = $t->dates('thisMonth', '2026-01-21');

check('thisMonth count', count($dates) === 21, count($dates) . ' dates');
check('thisMonth first', $dates[0] === '2026-01-01', $dates[0]);

// A full week stays complete even when it is the week of the running day

$dates = $t->dates('lastWeek', $today->format('Y-m-d'));
check('lastWeek is always complete', count($dates) === 7, count($dates) . ' dates');

// 4) Periods that have no completed day yet

$monday = $today->modify('monday this week')->format('Y-m-d') === $today->format('Y-m-d');

if( $monday )
  check('thisWeek on a monday is empty', $t->dates('thisWeek', $today->format('Y-m-d')) === []);
else
  echo "  SKIP  thisWeek on a monday is empty (only runs on a monday)\n";

// 5) An unknown range key is reported, not silently treated as a day

check('unknown range', $t->dates('lastYear', '2026-01-21') === null);

// 6) Aggregation over fixture day files

$user = '_test_range_nutrients';
$dir  = "data/users/$user/days";

if( ! is_dir($dir))
  mkdir( $dir, 0777, true);

// Two logged days, the third one has no file at all (counts as a day without entries)

file_put_contents("$dir/2026-01-05.tsv",
  "unprecise: false\n\n" .   // header block, must not end up in the entries
  "08:00:00  F  Apple  52  0.2  14  0.3  0  0.5  {amount: {label: \"1\", weight: 100}, fibre: 2, sugar: 10, vit: {C: 5}, min: {Fe: 1}}\n");

file_put_contents("$dir/2026-01-06.tsv",
  "08:00:00  F  Apple  52  0.2  14  0.3  0  0.5  {amount: {label: \"1\", weight: 100}, fibre: 2, sugar: 10, vit: {C: 5}, min: {Fe: 1}}\n" .
  "09:00:00  S  Pill   0   0    0   0    0  0.1  {amount: {label: \"1\", weight: 1}, vit: {C: 3}}\n" .
  "10:00:00  W  Sport  0   0    0   0    0  0    {amount: {label: \"1\", weight: 0}, vit: {C: 99}}\n");   // no food type, must be ignored

config::instance( new SimpleData(['defaultUser' => $user]));

$result = $t->getRangeNutrients(['range' => '3days', 'date' => '2026-01-07']);
$data   = $result['data'];
$foods  = [];

foreach( $data['foods'] as $food )
  $foods[$food['food']] = $food['nutrients'];

check('aggregation succeeds', $result['result'] === 'success');
check('days in range',        $data['daysInRange']  === 3, (string) $data['daysInRange']);
check('days with data',       $data['daysWithData'] === 2, (string) $data['daysWithData']);

check('one entry per food', count($data['foods']) === 2, count($data['foods']) . ' foods');
check('non food type ignored', ! isset($foods['Sport']));

// Daily average: the sum of the range divided by the days it spans

check('avg of a nested value', round( $foods['Apple']['vit']['C'], 5) === round( 10 / 3, 5),
      (string) $foods['Apple']['vit']['C']);
check('avg of a top level value', round( $foods['Apple']['fibre'], 5) === round( 4 / 3, 5),
      (string) $foods['Apple']['fibre']);
check('avg of a supplement', round( $foods['Pill']['vit']['C'], 5) === 1.0,
      (string) $foods['Pill']['vit']['C']);
check('sugar is kept', round( $foods['Apple']['sugar'], 5) === round( 20 / 3, 5));
check('amount is no nutrient', ! isset( $foods['Apple']['amount']));

// A range without a completed day returns no foods, the browser then zeroes the rows

if( $monday )
{
  $empty = $t->getRangeNutrients(['range' => 'thisWeek', 'date' => $today->format('Y-m-d')]);

  check('empty range has no days',  $empty['data']['daysInRange'] === 0);
  check('empty range has no foods', $empty['data']['foods'] === []);
}
else
  echo "  SKIP  empty range has no days (only runs on a monday)\n";

// An unknown range reaches the browser as an error

$bad = $t->getRangeNutrients(['range' => 'lastYear', 'date' => '2026-01-07']);
check('unknown range is an error', $bad['result'] === 'error' && ! empty( $bad['data']['message']));

// Clean up the fixtures

foreach( scandir($dir) as $file )
  if( ! in_array( $file, ['.', '..']))  unlink("$dir/$file");

rmdir( $dir);
rmdir( "data/users/$user");

echo "\n  $pass passed, $fail failed\n";

exit( $fail ? 1 : 0);

?>
