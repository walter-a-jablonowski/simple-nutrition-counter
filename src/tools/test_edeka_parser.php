<?php

/*

Standalone tests for EdekaParser extraction and format-change detection.
Run from the `src` directory:  php tools/test_edeka_parser.php

*/

chdir( dirname(__DIR__));  // run relative to src/ so the lib require paths resolve

require_once 'lib/food_import/EdekaParser.php';

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

// Build an EDEKA-style page: the product record lives in the __NEXT_DATA__ blob

function edekaPage( array $data, array $query = ['slug' => ['unsere-marken', 'produkte', 'gut-guenstig-zartbitter-schokolade-4311501713570']], string $extraHtml = '') : string
{
  $page =
  [
    'props' => ['pageProps' => ['moduleProps' => ['props' => ['data' => ['data' => $data]]]]],
    'query' => $query,
  ];

  return '<html><body>' . $extraHtml
       . '<script id="__NEXT_DATA__" type="application/json">' . json_encode($page) . '</script>'
       . '</body></html>';
}

// A nutrient entry as EDEKA writes it: per-100 g basis in `norm`, per-serving in `portion`

function nutrient( ?float $norm, ?float $portion = null ) : array
{
  $entry = [];

  if( $norm !== null )
    $entry['norm'] = ['size' => 'je 100 g (unzubereitet)', 'value' => $norm];

  if( $portion !== null )
    $entry['portion'] = ['size' => 'je Portion (unzubereitet)', 'value' => $portion];

  return $entry;
}

// Real values from www.edeka.de/.../gut-guenstig-zartbitter-schokolade-4311501713570.jsp

$goodData =
[
  'pageHeading'   => 'Zartbitter-Schokolade',
  'brand'         => ['name' => 'GUT&GÜNSTIG'],
  'volume'        => ['sellingContent' => '100 g'],
  // label block every EDEKA product page carries — one of the markers matches() looks for
  'globalContent' => ['gcProductDetailsGeneral' => ['pt_resp_product_detail_general_nutrition_headline' => 'Nährwertangaben']],
  'ingredients' =>
  [
    'ingredients'          => "Zutaten: Kakaomasse**, Zucker, LAKTOSE, Emulgator: Lecithine (enthält SOJA); Vanilleextrakt.\n\n"
                            . 'Das Produkt kann Spuren von MILCH, EIERN, ERDNÜSSEN, GLUTEN und SCHALENFRÜCHTEN enthalten.',
    'allergensDescription' => 'Sojabohnen und daraus hergestellte Erzeugnisse, Milch und daraus hergestellte Erzeugnisse (einschließlich Laktose)',
    'additives'            => ['traces' => ['Eier und daraus hergestellte Erzeugnisse', 'Erdnüsse und daraus hergestellte Erzeugnisse']],
  ],
  'nutrition' =>
  [
    'kcal'         => nutrient(512, 86),
    'kj'           => nutrient(2138, 357),
    'fat'          => nutrient(28, 4.7),
    'fatSaturated' => nutrient(18, 3),
    'carb'         => nutrient(53, 8.9),
    'carbSugar'    => nutrient(47, 7.8),
    'fibre'        => nutrient(8.5, 1.4),
    'protein'      => nutrient(7.8, 1.3),
    'salt'         => nutrient(0.01, 0.01),
  ],
];

$parser = new EdekaParser();

// 1) Healthy page parses cleanly

$food = $parser->parse( edekaPage($goodData), null);

check('good page: name',        ($food['name'] ?? null) === 'Zartbitter-Schokolade', 'name=' . var_export($food['name'] ?? null, true));
check('good page: productName', ($food['productName'] ?? null) === 'GUT&GÜNSTIG Zartbitter-Schokolade 100g', 'productName=' . var_export($food['productName'] ?? null, true));
check('good page: vendor',      ($food['vendor'] ?? null) === 'Edeka');
check('good page: weight "100 g" -> "100g"', ($food['weight'] ?? null) === '100g', 'weight=' . var_export($food['weight'] ?? null, true));
check('good page: calories',    ($food['calories'] ?? null) == 512, 'calories=' . var_export($food['calories'] ?? null, true));
check('good page: all 7 nutrients parsed', count($food['nutritionalValues']) === 7, 'count=' . count($food['nutritionalValues']));
check('good page: url rebuilt from slug', ($food['url'] ?? '') === 'https://www.edeka.de/unsere-marken/produkte/gut-guenstig-zartbitter-schokolade-4311501713570/', 'url=' . var_export($food['url'] ?? null, true));

// 2) Ingredients, allergens and traces land in their own fields

check('ingredients: "Zutaten:" prefix stripped', str_starts_with($food['ingredients'] ?? '', 'Kakaomasse**, Zucker'), 'ingredients=' . var_export($food['ingredients'] ?? null, true));
check('ingredients: trace note split off',       stripos($food['ingredients'] ?? '', 'Spuren') === false, 'ingredients=' . var_export($food['ingredients'] ?? null, true));
check('allergy from allergensDescription',       str_starts_with($food['allergy'] ?? '', 'Sojabohnen und daraus'), 'allergy=' . var_export($food['allergy'] ?? null, true));
check('mayContain from structured traces',       ($food['mayContain'] ?? null) === 'Eier und daraus hergestellte Erzeugnisse; Erdnüsse und daraus hergestellte Erzeugnisse', 'mayContain=' . var_export($food['mayContain'] ?? null, true));

// 3) Without a structured traces list, the note is taken from the ingredient text

$noTraces = $goodData;
unset($noTraces['ingredients']['additives']);

$food = $parser->parse( edekaPage($noTraces), null);
check('mayContain falls back to the ingredient note', stripos($food['mayContain'] ?? '', 'Das Produkt kann Spuren von MILCH') === 0, 'mayContain=' . var_export($food['mayContain'] ?? null, true));

// 4) A nutrient EDEKA has no value for is an empty object -> simply absent

$noFibre = $goodData;
$noFibre['nutrition']['fibre'] = [];

$food = $parser->parse( edekaPage($noFibre), null);
check('empty nutrient block -> key omitted, rest intact', ! isset($food['nutritionalValues']['fibre']) && count($food['nutritionalValues']) === 6, 'values=' . json_encode($food['nutritionalValues']));

// 5) A portion-only figure must never stand in for the per-100 g value

$portionOnly = $goodData;
$portionOnly['nutrition']['fibre'] = nutrient(null, 1.4);

$food = $parser->parse( edekaPage($portionOnly), null);
check('portion-only nutrient is not used as per-100g value', ! isset($food['nutritionalValues']['fibre']), 'fibre=' . var_export($food['nutritionalValues']['fibre'] ?? null, true));

// 6) Bio brand line sets the bio certificate, GUT&GÜNSTIG does not

check('non-bio brand: no certificates', ($food['certificates'] ?? null) === []);

$bio = $goodData;
$bio['brand']['name'] = 'EDEKA Bio';

$food = $parser->parse( edekaPage($bio), null);
check('bio brand sets bio certificate', ($food['certificates']['bio'] ?? false) === true, 'certificates=' . json_encode($food['certificates']));

// 7) JSON nutrition gone, but the page still renders its nutrient table -> fallback

$tableHtml = '<table><thead><tr><th>Durchschnittliche Nährwerte</th><th>je 100 ml bzw. g</th></tr></thead><tbody>'
  . '<tr><td>Brennwert in kcal</td><td>512</td><td>0</td></tr>'
  . '<tr><td>Brennwert in kJ</td><td>2138</td><td>0</td></tr>'
  . '<tr><td>Fett in g</td><td>28</td><td>0</td></tr>'
  . '<tr><td>Fett, davon gesättigte Fettsäuren in g</td><td>18</td><td>0</td></tr>'
  . '<tr><td>Kohlenhydrate in g</td><td>53</td><td>0</td></tr>'
  . '<tr><td>Kohlenhydrate, davon Zucker in g</td><td>47</td><td>0</td></tr>'
  . '<tr><td>Eiweiß in g</td><td>7.8</td><td>0</td></tr>'
  . '<tr><td>Salz in g</td><td>0.01</td><td>0</td></tr>'
  . '</tbody></table>';

$noNutrition = $goodData;
unset($noNutrition['nutrition']);

$food = $parser->parse( edekaPage($noNutrition, ['slug' => ['a']], $tableHtml), null);

check('table fallback: calories from kcal row (not kJ)', ($food['calories'] ?? null) == 512, 'calories=' . var_export($food['calories'] ?? null, true));
check('table fallback: "Fett, davon ..." not swallowed by "Fett"',
  ($food['nutritionalValues']['fat'] ?? null) == 28 && ($food['nutritionalValues']['saturatedFat'] ?? null) == 18,
  'values=' . json_encode($food['nutritionalValues']));
check('table fallback: sugar row', ($food['nutritionalValues']['sugar'] ?? null) == 47, 'values=' . json_encode($food['nutritionalValues']));

// 8) Format change: no nutrition JSON and no matching table -> throw, never import empty

$threw = false;

try {
  $parser->parse( edekaPage($noNutrition), null);
}
catch( Exception $e ) {
  $threw = true;
}

check('changed format: throws instead of returning empty nutrition', $threw, 'no exception thrown — silent failure');

// 9) A page without the product blob is reported, not silently half-imported

$threw = false;

try {
  $parser->parse('<html><body>Seite nicht gefunden</body></html>', 'https://www.edeka.de/irgendwas.jsp');
}
catch( Exception $e ) {
  $threw = true;
}

check('non-product edeka page: throws', $threw, 'no exception thrown');

// 10) Vendor detection must work both ways round

$reweHtml = '{"productName":"Test Linseneintopf","grammage":"800g","nutritionFacts":[{"servingSize":{"value":100}}]}';

check('matches: edeka url',            $parser->matches('', 'https://www.edeka.de/unsere-marken/produkte/x-123/') === true);
check('matches: edeka html markers',   $parser->matches( edekaPage($goodData), null) === true);
check('matches: rejects a REWE page',  $parser->matches($reweHtml, 'https://www.rewe.de/shop/p/x/1') === false);

require_once 'lib/food_import/ReweParser.php';
$rewe = new ReweParser();

check('matches: ReweParser rejects an EDEKA page', $rewe->matches( edekaPage($goodData), 'https://www.edeka.de/unsere-marken/produkte/x-123/') === false);

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
