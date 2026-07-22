<?php

/*

Standalone tests for the layout place/move array helpers (no filesystem).
Run from the `src` directory:  php tools/test_layout_functions.php

*/

chdir( dirname(__DIR__));

require_once 'vendor/autoload.php';   // Symfony Yaml is pulled in by functions.php
require_once 'models/functions.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

function sampleLayout() : array
{
  return
  [
    'Meals' =>
    [
      '(first_entries)'                          => ['list' => ['Apple', 'Bread']],
      'Nuts, seeds and berries (color:#ffa500)'  => ['list' => ['Mandel R Bio', 'Cashew N']],
      'Empty group'                              => ['list' => []],
    ],
    'On the go' =>
    [
      'Common (color:#ffa500)' => ['list' => ['Water']],
    ],
  ];
}

// 1) Display name strips the attrib part, keeps (first_entries)

check('display name strips (color:...)', layout_group_display_name('Nuts, seeds and berries (color:#ffa500)') === 'Nuts, seeds and berries');
check('display name keeps (first_entries)', layout_group_display_name('(first_entries)') === '(first_entries)');

// 2) contains_food

$l = sampleLayout();
check('contains_food finds existing', layout_contains_food($l, 'Water') === true);
check('contains_food misses absent', layout_contains_food($l, 'Nope') === false);

// 3) insert by tab + display name (attrib key matched by display name)

$l = sampleLayout();
$ok = layout_insert_food($l, 'Meals', 'Nuts, seeds and berries', 'Walnuss');
check('insert into attrib group by display name', $ok && in_array('Walnuss', $l['Meals']['Nuts, seeds and berries (color:#ffa500)']['list'], true));

// 4) insert prepend into first_entries

$l = sampleLayout();
layout_insert_food($l, 'Meals', '(first_entries)', 'Zucchini', true);
check('prepend puts item on top', ($l['Meals']['(first_entries)']['list'][0] ?? null) === 'Zucchini');

// 5) insert into an unknown group fails

$l = sampleLayout();
check('insert into unknown group returns false', layout_insert_food($l, 'Meals', 'Ghost group', 'X') === false);

// 6) remove clears the food from every list

$l = sampleLayout();
$l['Meals']['(first_entries)']['list'][] = 'Water';  // Water now in two places
layout_remove_food($l, 'Water');
check('remove clears all occurrences',
  ! in_array('Water', $l['On the go']['Common (color:#ffa500)']['list'], true)
  && ! in_array('Water', $l['Meals']['(first_entries)']['list'], true));

// 7) move = remove everywhere + insert into target (simulated on the array)

$l = sampleLayout();
layout_remove_food($l, 'Apple');
layout_insert_food($l, 'On the go', 'Common', 'Apple');
check('move relocates the food',
  ! in_array('Apple', $l['Meals']['(first_entries)']['list'], true)
  && in_array('Apple', $l['On the go']['Common (color:#ffa500)']['list'], true));

// 8) no duplicate on re-insert

$l = sampleLayout();
layout_insert_food($l, 'On the go', 'Common', 'Water');
$count = count( array_filter($l['On the go']['Common (color:#ffa500)']['list'], fn($n) => $n === 'Water'));
check('no duplicate when already present', $count === 1, "count=$count");

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
