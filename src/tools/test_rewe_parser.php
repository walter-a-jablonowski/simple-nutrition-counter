<?php

/*

Standalone tests for ReweParser format-change detection.
Run from the `src` directory:  php tools/test_rewe_parser.php

*/

chdir( dirname(__DIR__));  // run relative to src/ so the lib require paths resolve

require_once 'lib/food_import/ReweParser.php';

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

// Minimal REWE-style page: has productName + the current per-100g nutritionFacts
// JSON block, so a healthy page must parse cleanly

$goodHtml = '{"productName":"Test Linseneintopf","regulatedProductName":"Linseneintopf",'
  . '"grammage":"800g","nutritionFacts":[{"servingSize":{"value":100},"nutrientInformation":['
  . '{"nutrientType":{"code":"ENER-"},"quantityContained":{"value":80,"uomShortText":"kcal"}},'
  . '{"nutrientType":{"code":"FAT"},"quantityContained":{"value":2.1,"uomShortText":"g"}},'
  . '{"nutrientType":{"code":"CHOAVL"},"quantityContained":{"value":9.5,"uomShortText":"g"}},'
  . '{"nutrientType":{"code":"PRO-"},"quantityContained":{"value":4.3,"uomShortText":"g"}}'
  . ']}]}';

// Same product, but the nutrition format has "changed": nutritionFacts block is
// gone and the HTML table markup no longer matches the fallback regex. Everything
// else (productName) is still present, so parse() does not hit its only guard.

$changedHtml = '{"productName":"Test Linseneintopf","regulatedProductName":"Linseneintopf",'
  . '"grammage":"800g"}'
  . '<table><tr><td>Energie</td><td>80 kcal</td></tr><tr><td>Fett</td><td>2,1 g</td></tr></table>';

$parser = new ReweParser();

// 1) Healthy page still parses and yields nutrition data

$food = $parser->parse($goodHtml, null);
check('good page: calories parsed', ($food['calories'] ?? null) == 80, 'calories=' . var_export($food['calories'] ?? null, true));
check('good page: core nutrients parsed', count($food['nutritionalValues']) >= 3, 'count=' . count($food['nutritionalValues']));

// 2) Changed format must be DETECTED (throw), not silently return empty nutrition

$threw = false;
$msg   = '';

try {
  $parser->parse($changedHtml, null);
}
catch( Exception $e ) {
  $threw = true;
  $msg   = $e->getMessage();
}

check('changed format: throws instead of returning empty nutrition', $threw, 'no exception thrown — silent failure');

// 3) A "Vegetarisch" flag (without "Vegan") must set the vegan certificate.
// Reuse the good page (valid nutrients) plus a CustomProductFlags entry.

$vegetarianHtml = '{"name":"CustomProductFlags","label":"Eigenschaften","value":"Tiefpreis, Vegetarisch"},' . $goodHtml;

$food = $parser->parse($vegetarianHtml, null);
check('vegetarisch flag sets vegan certificate', ($food['certificates']['vegan'] ?? false) === true, 'certificates=' . json_encode($food['certificates'] ?? []));

// 4) A "N Stück" count in the product title must be imported as `pieces`.

$piecesHtml = str_replace('"productName":"Test Linseneintopf"', '"productName":"Lawa Feine Pfannkuchen 300g, 5 Stück"', $goodHtml);

$food = $parser->parse($piecesHtml, null);
check('title "5 Stück" -> pieces=5', ($food['pieces'] ?? null) === 5, 'pieces=' . var_export($food['pieces'] ?? null, true));

// 5) A single-item product (no "Stück") leaves pieces unset (null)

$food = $parser->parse($goodHtml, null);
check('no piece count -> pieces null', ($food['pieces'] ?? null) === null, 'pieces=' . var_export($food['pieces'] ?? null, true));

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
