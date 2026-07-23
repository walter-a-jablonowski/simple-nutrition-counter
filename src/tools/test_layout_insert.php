<?php

/*

Verify layout_insert_food() positioning: append, top and "insert behind an
entry", plus that move_food_in_layout()'s remove + insert keeps the order sane.
Run from src:  php tools/test_layout_insert.php

*/

chdir( dirname(__DIR__));

require_once 'vendor/autoload.php';
require_once 'lib/helper.php';
require_once 'models/functions.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

// A layout with an attrib key, to be sure the display-name matching still works

function sample_layout()
{
  return [
    'Meals' => [
      '(first_entries)'              => ['list' => ['Quick 1']],
      'Nuts (color:#ffa500)'         => ['list' => ['Mandel', 'Walnuss', 'Cashew']]
    ],
    'Misc' => [
      'Empty group'                  => ['list' => []]
    ]
  ];
}

$list = function( $layout ) { return $layout['Meals']['Nuts (color:#ffa500)']['list']; };

// 1) null appends

$l = sample_layout();
layout_insert_food($l, 'Meals', 'Nuts', 'Neu', null);
check('null appends', $list($l) === ['Mandel', 'Walnuss', 'Cashew', 'Neu'], implode(',', $list($l)));

// 2) '' puts it on top

$l = sample_layout();
layout_insert_food($l, 'Meals', 'Nuts', 'Neu', '');
check("'' inserts at the top", $list($l) === ['Neu', 'Mandel', 'Walnuss', 'Cashew'], implode(',', $list($l)));

// 3) a name inserts directly behind it

$l = sample_layout();
layout_insert_food($l, 'Meals', 'Nuts', 'Neu', 'Mandel');
check('inserts behind the named entry', $list($l) === ['Mandel', 'Neu', 'Walnuss', 'Cashew'], implode(',', $list($l)));

// 4) behind the last entry

$l = sample_layout();
layout_insert_food($l, 'Meals', 'Nuts', 'Neu', 'Cashew');
check('inserts behind the last entry', $list($l) === ['Mandel', 'Walnuss', 'Cashew', 'Neu'], implode(',', $list($l)));

// 5) unknown anchor falls back to append instead of losing the food

$l = sample_layout();
layout_insert_food($l, 'Meals', 'Nuts', 'Neu', 'Gone');
check('unknown anchor appends', $list($l) === ['Mandel', 'Walnuss', 'Cashew', 'Neu'], implode(',', $list($l)));

// 6) an empty group takes the food whatever the position says

$l = sample_layout();
layout_insert_food($l, 'Misc', 'Empty group', 'Neu', '');
check('empty group accepts the food', $l['Misc']['Empty group']['list'] === ['Neu']);

// 7) already present -> untouched, still reports success

$l = sample_layout();
$ok = layout_insert_food($l, 'Meals', 'Nuts', 'Walnuss', '');
check('duplicate is a no-op', $ok && $list($l) === ['Mandel', 'Walnuss', 'Cashew'], implode(',', $list($l)));

// 8) unknown tab / group is reported

$l = sample_layout();
check('unknown group returns false', layout_insert_food($l, 'Meals', 'Nope', 'Neu', '') === false);
check('unknown tab returns false',   layout_insert_food($l, 'Nope',  'Nuts', 'Neu', '') === false);

// 9) reorder inside the same group: remove first, then insert behind an anchor

$l = sample_layout();
layout_remove_food($l, 'Cashew');
layout_insert_food($l, 'Meals', 'Nuts', 'Cashew', 'Mandel');
check('reorder within the group', $list($l) === ['Mandel', 'Cashew', 'Walnuss'], implode(',', $list($l)));

// 10) add_food_to_layout's convention still holds: first_entries = top, others = append

$l = sample_layout();
layout_insert_food($l, 'Meals', '(first_entries)', 'Neu', '');
check('first_entries keeps newest on top', $l['Meals']['(first_entries)']['list'] === ['Neu', 'Quick 1']);

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
