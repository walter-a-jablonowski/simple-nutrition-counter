<?php

/*

Standalone tests for FoodValidator required-field enforcement.
Run from the `src` directory:  php tools/test_food_validator.php

*/

chdir( dirname(__DIR__));  // run relative to src/ so require paths resolve

require_once 'lib/food_import/FoodValidator.php';

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

// A complete, valid record

$complete =
[
  'name'     => 'Test food',
  'weight'   => '800g',
  'calories' => 69,
  'nutritionalValues' => ['fat' => 0.7, 'carbs' => 10.2, 'sugar' => 0.9, 'amino' => 4, 'salt' => 0.85],
];

// 1) Complete record: nothing missing

check('complete record: no missing fields', FoodValidator::missingRequired($complete) === [], 'got ' . json_encode(FoodValidator::missingRequired($complete)));

// 2) Missing calories and salt are reported

$partial = $complete;
unset($partial['calories'], $partial['nutritionalValues']['salt']);
check('missing calories + salt reported', FoodValidator::missingRequired($partial) === ['calories', 'salt'], 'got ' . json_encode(FoodValidator::missingRequired($partial)));

// 3) Zero is a valid value (e.g. fat 0 for water), not "missing"

$zeros = $complete;
$zeros['nutritionalValues']['fat'] = 0;
$zeros['calories'] = 0;
check('zero values count as present', FoodValidator::missingRequired($zeros) === [], 'got ' . json_encode(FoodValidator::missingRequired($zeros)));

// 4) Empty-string weight counts as missing

$blankWeight = $complete;
$blankWeight['weight'] = '   ';
check('blank weight reported', in_array('weight', FoodValidator::missingRequired($blankWeight), true), 'got ' . json_encode(FoodValidator::missingRequired($blankWeight)));

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
