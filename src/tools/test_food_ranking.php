<?php

/*

Standalone tests for the advisor's food shortlist (see lib/advisor/FoodRanking.php).

Run from the `src` directory:  php tools/test_food_ranking.php

Offline, no network, no cost. Hand made report and grid, so the numbers in the checks
can be worked out on paper.

What it watches:

- only foods with a reference panel become candidates
- the gain cap, which is what stops one huge food from winning against three gaps
- the penalty for nutrients that are already over, and for eating the same food daily
- water is never chased, no matter how short it reads
- the contributors of an over-limit nutrient, over all foods

*/

chdir( dirname(__DIR__));  // run relative to src/ so the require paths resolve

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/advisor/FoodRanking.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

// A report row in the shape NutrientReport produces

function row( string $group, string $short, string $name, float $perDay, float $lower,
              float $ideal, float $upper, bool $measurable = true ) : array
{
  $status = $perDay < $lower ? 'below' : ($perDay > $upper ? 'above' : 'ok');

  return [
    'group' => $group, 'groupName' => $group, 'nutrient' => $name, 'short' => $short,
    'unit' => 'mg', 'perDay' => $perDay, 'lower' => $lower, 'ideal' => $ideal,
    'upper' => $upper, 'status' => $status,
    'gap'  => $status === 'below' ? round( $ideal - $perDay, 5) : null,
    'over' => $status === 'above' ? round( $perDay - $upper, 5) : null,
    'coverage' => $measurable ? 80 : 10, 'measurable' => $measurable
  ];
}

$report = [
  'nutrients' => [
    row('vit', 'C',    'Vitamin C', 10,   90,  100, 200),         // below, gap 90
    row('vit', 'D',    'Vitamin D',  0, 0.018, 0.02, 0.05),       // below, gap 0.02
    row('min', 'Fe',   'Iron',       2,    9,   10,  20),         // below, gap 8
    row('min', 'Se',   'Selenium',   0,    5,   10,  20, false),  // below but unmeasurable
    row('min', 'NaCl', 'Salt',       9,    4,    5,   6),         // above, over 3
    row('misc', 'H2O', 'Water',    200, 2500, 3000, 4000),        // below, but ignored
    row('vit', 'A',    'Vitamin A',  1,  0.8,    1, 1.5)          // ok
  ],
  'byFood' => [
    'Allrounder' => ['count' => 0, 'calories' => 0, 'sums' => []],
    'Salt bomb'  => ['count' => 0, 'calories' => 0, 'sums' => ['min' => ['NaCl' => 6.0]]],
    'Daily'      => ['count' => 6, 'calories' => 300, 'sums' => ['min' => ['NaCl' => 2.0]]],
    'Untyped'    => ['count' => 3, 'calories' => 500, 'sums' => ['min' => ['NaCl' => 1.0]]]
  ]
];

/* The grid. Three amounts each so the middle one is picked, and the middle one is what
   every number below is worked out from */

function amounts( array $mid ) : array
{
  $small = ['weight' => 1, 'calories' => 1, 'price' => 0.1, 'nutriVal' => [], 'vit' => [], 'min' => [], 'misc' => []];
  $large = $small;

  return ['small' => $small, 'middle' => $mid + $small, 'large' => $large];
}

$layout = new SimpleData([

  // Closes a bit of all three gaps: 45/90 + 0.01/0.02 + 4/8 = 1.5
  'Allrounder' => amounts(['weight' => 100, 'calories' => 50, 'price' => 1.0,
                           'vit' => ['C' => 45, 'D' => 0.01], 'min' => ['Fe' => 4]]),

  // Huge in one nutrient only. Capped at the gap: 90/90 = 1.0, so it loses to Allrounder
  'One trick'  => amounts(['weight' => 100, 'calories' => 50, 'price' => 1.0,
                           'vit' => ['C' => 9000]]),

  // Same gain as Allrounder but brings 3 g of salt on top: 1.5 - 3/6 = 1.0
  'Salt bomb'  => amounts(['weight' => 100, 'calories' => 50, 'price' => 1.0,
                           'vit' => ['C' => 45, 'D' => 0.01], 'min' => ['Fe' => 4, 'NaCl' => 3]]),

  // Same gain as Allrounder, but eaten 6 times in the range: 1.5 - 6*0.15 = 0.6
  'Daily'      => amounts(['weight' => 100, 'calories' => 50, 'price' => 1.0,
                           'vit' => ['C' => 45, 'D' => 0.01], 'min' => ['Fe' => 4]]),

  // Would top the list, but carries no type
  'Untyped'    => amounts(['weight' => 100, 'calories' => 50, 'price' => 1.0,
                           'vit' => ['C' => 90, 'D' => 0.02], 'min' => ['Fe' => 8]]),

  // Closes nothing that is open, only water and a nutrient that is fine
  'Water only' => amounts(['weight' => 100, 'calories' => 0, 'price' => 0.1,
                           'misc' => ['H2O' => 900], 'vit' => ['A' => 5]])
]);

$foods = new SimpleData([
  'Allrounder' => ['type' => 'Broccoli', 'vendor' => 'Rewe',     'usedAmounts' => ['25g','50g','100g']],
  'One trick'  => ['type' => 'Peppers',  'vendor' => 'none',     'usedAmounts' => ['1']],
  'Salt bomb'  => ['type' => 'Cheese',   'vendor' => 'Aldi',     'usedAmounts' => ['1'], 'acceptable' => 'less'],
  'Daily'      => ['type' => 'Lentils',  'vendor' => 'Rewe',     'usedAmounts' => ['1'], 'careful' => true],
  'Untyped'    => [                      'vendor' => 'Norma',    'usedAmounts' => ['1']],
  'Water only' => ['type' => 'Water',    'vendor' => 'multiple', 'usedAmounts' => ['1']]
]);

$result = (new FoodRanking( $layout, $foods, 40))->select( $report );

$byName = [];
foreach( $result['candidates'] as $c )
  $byName[$c['food']] = $c;

// 1) Which nutrients are worth acting on

$deficits = array_column( $result['deficits'], 'short');

check('deficit list',        $deficits === ['C', 'D', 'Fe'], implode(', ', $deficits));
check('unmeasurable is out', ! in_array('Se', $deficits));
check('water is never chased', ! in_array('H2O', $deficits));
check('nutrient inside its bounds is out', ! in_array('A', $deficits));

$excesses = array_column( $result['excesses'], 'short');
check('excess list', $excesses === ['NaCl'], implode(', ', $excesses));

// 2) Only foods with a reference panel are offered

check('untyped food is no candidate', ! isset( $byName['Untyped']));
check('food that closes nothing is out', ! isset( $byName['Water only']));
check('candidate count', count( $result['candidates']) === 4, count( $result['candidates']) . ' candidates');

/* 3) The scores, worked out on paper. Every middle amount here is 50 kcal, under the
      floor, so it is scored as 100 kcal and the density equals the raw net - which is
      what makes these numbers readable. The heavy portion below is the real check */

// 45/90 + 0.01/0.02 + 4/8 = 1.5
check('gain over three gaps', $byName['Allrounder']['score'] === 1.5, (string) $byName['Allrounder']['score']);

// 9000 mg of vitamin C is worth exactly the gap and no more: 90/90 = 1.0
check('gain is capped at the gap', $byName['One trick']['score'] === 1.0, (string) $byName['One trick']['score']);

// 1.5 less 3 g of salt: share 3/6 = 0.5, weighted by how far over it is (3/6) = 0.25
check('penalty for an excess', $byName['Salt bomb']['score'] === 1.25, (string) $byName['Salt bomb']['score']);

// 1.5, eaten 6 times: / (1 + 6*0.15) = 0.789
check('penalty for repetition', $byName['Daily']['score'] === 0.789, (string) $byName['Daily']['score']);

// A big portion does not win by being big: same food at 500 kcal scores a fifth
$fat = new SimpleData(['Heavy' => amounts(['weight' => 500, 'calories' => 500, 'price' => 1.0,
                                           'vit' => ['C' => 45, 'D' => 0.01], 'min' => ['Fe' => 4]])]);
$fatFoods = new SimpleData(['Heavy' => ['type' => 'Stew', 'vendor' => 'Rewe', 'usedAmounts' => ['1']]]);
$heavy = (new FoodRanking( $fat, $fatFoods, 40))->select( $report )['candidates'][0];

check('scored per calorie', $heavy['score'] === 0.3, (string) $heavy['score']);   // 1.5 / 500 * 100

// 4) Best first, so the cap really decides the order

check('best first', $result['candidates'][0]['food'] === 'Allrounder', $result['candidates'][0]['food']);
check('worst last', end( $result['candidates'])['food'] === 'Daily', end( $result['candidates'])['food']);

// 5) What a candidate carries

$all = $byName['Allrounder'];

check('closes the open gaps', $all['closes'] === ['Vitamin C' => 45.0, 'Vitamin D' => 0.01, 'Iron' => 4.0],
      json_encode( $all['closes']));
check('raises nothing',   $all['raises'] === []);
check('middle amount',    $all['amount'] === 'middle', $all['amount']);
check('amounts of the food', $all['amounts'] === ['25g','50g','100g']);
check('vendor kept',      $all['vendor'] === 'Rewe', $all['vendor']);
check('placeholder vendor dropped', $byName['One trick']['vendor'] === '', $byName['One trick']['vendor']);

check('raises is filled when it does', $byName['Salt bomb']['raises'] === ['Salt' => 3.0],
      json_encode( $byName['Salt bomb']['raises']));

/* A nutrient a food barely touches is not worth naming: 0.2 g of salt is 3 % of the
   6 g bound, under the floor */

$trace = new SimpleData(['Trace' => amounts(['weight' => 100, 'calories' => 50, 'price' => 1.0,
                                             'vit' => ['C' => 45], 'min' => ['NaCl' => 0.2]])]);
$traceFoods = new SimpleData(['Trace' => ['type' => 'Broccoli', 'vendor' => 'Rewe', 'usedAmounts' => ['1']]]);
$traceOut = (new FoodRanking( $trace, $traceFoods, 40))->select( $report )['candidates'][0];

check('a trace of an excess is not named', $traceOut['raises'] === [], json_encode( $traceOut['raises']));

// Flags travel only when the food sets them

check('flag travels',  $byName['Salt bomb']['flags'] === ['acceptable' => 'less'], json_encode( $byName['Salt bomb']['flags']));
check('no empty flags', $byName['Allrounder']['flags'] === [], json_encode( $byName['Allrounder']['flags']));
check('eaten count',   $byName['Daily']['eaten'] === 6, (string) $byName['Daily']['eaten']);

// 6) Where the salt came from - over every food, panel or not

$salt = $result['contributors']['NaCl'];

check('contributors sorted', array_column( $salt, 'food') === ['Salt bomb', 'Daily', 'Untyped'],
      implode(', ', array_column( $salt, 'food')));
check('untyped food is a contributor', in_array('Untyped', array_column( $salt, 'food')));
check('contributor value',  $salt[0]['perDay'] === 6.0, (string) $salt[0]['perDay']);
check('food with none of it is out', ! in_array('Allrounder', array_column( $salt, 'food')));

// 7) maxFoods cuts the list

$short = (new FoodRanking( $layout, $foods, 2))->select( $report );
check('maxFoods cuts', count( $short['candidates']) === 2, count( $short['candidates']) . ' candidates');
check('maxFoods keeps the best', $short['candidates'][0]['food'] === 'Allrounder');

// 8) Nothing open: no candidates, and no crash

$fine = ['nutrients' => [ row('vit', 'A', 'Vitamin A', 1, 0.8, 1, 1.5)], 'byFood' => []];
$none = (new FoodRanking( $layout, $foods, 40))->select( $fine );

check('nothing to close', $none['candidates'] === [] && $none['deficits'] === [] && $none['excesses'] === []);

echo "\n  $pass passed, $fail failed\n";

exit( $fail ? 1 : 0);

?>
