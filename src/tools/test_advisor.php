<?php

/*

Standalone tests for the advisor's model step (see lib/advisor/NutritionAdvisor.php).

Run from the `src` directory:

  php tools/test_advisor.php           offline, no network, no cost
  php tools/test_advisor.php --prompt  print the prompt the real data would produce
  php tools/test_advisor.php --live    one real call against the real data, costs money

The offline mode replays a recorded answer through the whole mapping, so a refactor
cannot silently regress it. It also builds the prompt from a hand made report, which is
where the numbers the model sees are decided.

What it cannot cover is whether the model answers well - that is the prompt's job, and
--live is how you look at it.

*/

use Symfony\Component\Yaml\Yaml;

chdir( dirname(__DIR__));   // run relative to src/ so the require paths resolve

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/frm/ConfigStatic_240323/config.php';
require_once 'lib/frm/User.php';
require_once 'lib/settings.php';
require_once 'lib/env.php';

config::instance(   new SimpleData( Yaml::parse( file_get_contents('config.yml'))));
settings::instance( new SimpleData( config::get('defaultSettings')));

$_SESSION['userId'] = config::get('defaultUser');

require_once 'lib/advisor/NutritionAdvisor.php';

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

// Reach the private prompt builder without widening the class for a test

function build( string $method, ...$args )
{
  $m = new ReflectionMethod('NutritionAdvisor', $method);
  $m->setAccessible( true );

  return $m->invoke( null, ...$args );
}


/* --live and --prompt run against the real app, everything else is fixtures */

if( in_array('--live', $argv) || in_array('--prompt', $argv))
{
  require_once 'lib/frm/Controller_240323/ControllerBase.php';
  require_once 'AppController.php';

  $app = new AppController();

  foreach( ['loadNutritionModels' => [], 'adviceInput' => [date('Y-m-d')], 'dietRules' => []] as $name => $args )
  {
    $m = new ReflectionMethod('AppController', $name);
    $m->setAccessible( true );
    $out[$name] = $m->invoke( $app, ...$args );
  }

  [$reports, $selection] = $out['adviceInput'];

  if( in_array('--prompt', $argv))
  {
    echo build('userText', $reports, $selection, $out['dietRules']), "\n";
    exit(0);
  }

  echo "Calling the model, this takes 20-40 s ...\n\n";

  $answer = NutritionAdvisor::analyse( $reports, $selection, $out['dietRules']);

  echo Yaml::dump( $answer, 6, 2), "\n";
  echo "Record it as tools/advisor/response_good.json to test the mapping offline.\n";
  exit(0);
}


// 1) The prompt: what the model is actually shown

$report = [
  'range' => '30days', 'days' => 30, 'daysWithData' => 28,
  'coverage' => ['vit' => 60, 'min' => 60, 'fat' => 0, 'carbs.fibre' => 100],
  'macros'   => ['calories' => 1470.36, 'salt' => 4.99],
  'nutrients' => [
    ['group' => 'carbs', 'groupName' => 'Carbs', 'nutrient' => 'Fibre', 'short' => 'fibre', 'unit' => 'g',
     'perDay' => 17.4, 'lower' => 25, 'ideal' => 40, 'upper' => 50, 'status' => 'below',
     'gap' => 22.6, 'over' => null, 'coverage' => 100, 'measurable' => true],
    ['group' => 'carbs', 'groupName' => 'Carbs', 'nutrient' => 'Sugar', 'short' => 'sugar', 'unit' => 'g',
     'perDay' => 12.9, 'lower' => 0, 'ideal' => 25, 'upper' => 50, 'status' => 'ok',
     'gap' => null, 'over' => null, 'coverage' => 31, 'measurable' => false],
    ['group' => 'fat', 'groupName' => 'Fatty acids', 'nutrient' => 'ALA', 'short' => 'ALA', 'unit' => 'g',
     'perDay' => 0, 'lower' => 1, 'ideal' => 2, 'upper' => 3, 'status' => 'below',
     'gap' => 2, 'over' => null, 'coverage' => 0, 'measurable' => false],
    ['group' => 'min', 'groupName' => 'Minerals', 'nutrient' => 'Salt', 'short' => 'NaCl', 'unit' => 'g',
     'perDay' => 9, 'lower' => 4, 'ideal' => 5, 'upper' => 6, 'status' => 'above',
     'gap' => null, 'over' => 3, 'coverage' => 100, 'measurable' => true]
  ]
];

$short = $report;
$short['range'] = '7days';
$short['days']  = 7;
$short['nutrients'][0]['perDay'] = 11.0;

$selection = [
  'deficits' => [ $report['nutrients'][0] ],
  'excesses' => [ $report['nutrients'][3] ],
  'candidates' => [
    ['food' => 'Brokkoli R', 'vendor' => 'Rewe', 'amount' => '100g', 'weight' => 100,
     'calories' => 32, 'price' => 0.4, 'amounts' => ['25g', '50g', '100g'], 'eaten' => 2,
     'score' => 3.1, 'closes' => ['Fibre' => 2.4], 'raises' => [],
     'flags' => ['acceptable' => 'less']],

    ['food' => 'Linsen R Bio', 'vendor' => 'Rewe', 'amount' => '1/3', 'weight' => 88,
     'calories' => 102, 'price' => 0.5, 'amounts' => ['1/4', '1/3', '1/2'], 'eaten' => 0,
     'score' => 2.4, 'closes' => ['Fibre' => 7.1], 'raises' => [], 'flags' => []],

    ['food' => 'Mandel R Bio', 'vendor' => 'Rewe', 'amount' => '25g', 'weight' => 25,
     'calories' => 154, 'price' => 0.6, 'amounts' => ['25g', '50g'], 'eaten' => 3,
     'score' => 1.2, 'closes' => ['Fibre' => 3.1], 'raises' => [], 'flags' => []]
  ],
  'contributors' => ['NaCl' => [
    ['food' => 'Salami R',      'perDay' => 2.5, 'eaten' => 4],
    ['food' => 'Vit C Abtei M', 'perDay' => 0.1, 'eaten' => 18]
  ]],
  'untyped' => [['food' => 'Quark R', 'caloriesPerDay' => 42.7, 'eaten' => 5]]
];

$prompt = build('userText', [$short, $report], $selection, "Avoid all processed food.");

check('both ranges are columns', str_contains( $prompt, '| 7days | 30days |'), substr($prompt, 0, 120));
check('measurable rows are in the table', str_contains( $prompt, '| Fibre | g | 11 | 17 |'), 'fibre row');
check('unmeasurable rows are not',        ! preg_match('/\| ALA \| g \|/', $prompt));

// A group where only some nutrients are out names them, or a fine value gets written off

check('whole group named on its own', str_contains( $prompt, '- Fatty acids: only 0 %'), 'fatty acids');
check('partial group names the nutrient', str_contains( $prompt, '- Carbs (Sugar): only 31 %'), 'carbs');

check('candidate is listed',   str_contains( $prompt, '**Brokkoli R** (Rewe)'));
check('candidate amounts',     str_contains( $prompt, 'other amounts: 25g | 50g | 100g'));
check('candidate closes',      str_contains( $prompt, 'closes: Fibre 2.4'));
check('candidate flags travel', str_contains( $prompt, 'acceptable: less'));
check('how often it was eaten', str_contains( $prompt, 'eaten 2x in the range'));

check('excess contributors',   str_contains( $prompt, 'Salt: Salami R (2.5 g/day, 4x)'));
check('untyped foods listed',  str_contains( $prompt, 'Quark R'));
check('diet rules are in',     str_contains( $prompt, 'Avoid all processed food.'));
check('rules are marked as binding', str_contains( $prompt, 'does not belong in'));

// 2) The recorded answer, through the whole mapping

$known  = NutritionAdvisor::knownFoods( $selection );
$result = NutritionAdvisor::fromAnswer(
  json_decode( file_get_contents('tools/advisor/response_good.json'), true), $known );

$advice = $result['advice'];

check('known foods from every list',
      $known === ['Brokkoli R', 'Linsen R Bio', 'Mandel R Bio', 'Salami R', 'Vit C Abtei M', 'Quark R'],
      implode(', ', $known));

check('summary kept',   str_starts_with( $advice['summary'], 'Über 30 Tage'), $advice['summary']);
check('data notes kept', count( $advice['dataNotes']) === 2, count( $advice['dataNotes']) . ' notes');
check('deficits kept',   count( $advice['deficits']) === 3);
check('deficit shape',   $advice['deficits'][0]['nutrient'] === 'Fibre' && $advice['deficits'][0]['comment'] !== '');
check('excesses kept',   count( $advice['excesses']) === 2);

/* The guarantee the whole shortlist exists for: the model named four foods and one of
   them does not exist, so it is dropped and reported rather than shown */

$foods = array_column( $advice['recommended'], 'food');

check('invented food is dropped',  ! in_array('Gibt es nicht', $foods), implode(', ', $foods));
check('real foods are kept',       $foods === ['Brokkoli R', 'Linsen R Bio', 'Mandel R Bio'], implode(', ', $foods));
check('the drop is reported',      count( $result['warnings']) === 1, implode(' | ', $result['warnings']));
check('the warning names it',      str_contains( $result['warnings'][0] ?? '', 'Gibt es nicht'));

check('recommendation shape', ($advice['recommended'][0]['amount'] ?? '') === '100g'
                           && ($advice['recommended'][0]['because'] ?? []) === ['Fibre', 'Calcium', 'Vitamin K']);

check('avoid list kept', ($advice['avoid'][0]['food'] ?? '') === 'Vit C Abtei M', json_encode( $advice['avoid']));

// 3) A name in the wrong case is the app's food, not a new one

$result = NutritionAdvisor::fromAnswer(
  ['summary' => 'x', 'recommended' => [['food' => 'brokkoli r', 'amount' => '1', 'because' => [], 'comment' => '']]],
  $known );

check('case insensitive match', ($result['advice']['recommended'][0]['food'] ?? '') === 'Brokkoli R',
      json_encode( $result['advice']['recommended']));

/* 4) The payload the panel gets. Its shape is a contract between the ajax handler and
      AdvisorController, written in two languages, so the fields the panel reads are
      listed here and the file is handed to tools/test_advisor_panel.mjs to render */

require_once 'lib/frm/Controller_240323/ControllerBase.php';
require_once 'AppController.php';

$app   = new AppController();
$panel = new ReflectionMethod('AppController', 'reportForPanel');
$panel->setAccessible( true );

$report30 = $report;
$payload  = [
  'advice'   => NutritionAdvisor::fromAnswer(
                  json_decode( file_get_contents('tools/advisor/response_good.json'), true), $known )['advice'],
  'warnings' => ['The model named a food that does not exist: "Gibt es nicht".'],
  'report'   => $panel->invoke( $app, [$short, $report30], $selection ),
  'cached'   => false
];

foreach( ['range', 'days', 'daysWithData', 'coverage', 'macros', 'deficits', 'excesses', 'candidates'] as $field )
  check("payload report.$field", array_key_exists( $field, $payload['report']), implode(', ', array_keys( $payload['report'])));

foreach( ['nutrient', 'unit', 'perDay', 'ideal', 'coverage'] as $field )
  check("payload deficit.$field", array_key_exists( $field, $payload['report']['deficits'][0] ?? []),
        json_encode( $payload['report']['deficits'][0] ?? null));

foreach( ['nutrient', 'unit', 'perDay', 'upper'] as $field )
  check("payload excess.$field", array_key_exists( $field, $payload['report']['excesses'][0] ?? []),
        json_encode( $payload['report']['excesses'][0] ?? null));

check('payload names the deficit the advice names',
      ($payload['report']['deficits'][0]['nutrient'] ?? '') === 'Fibre'
      && in_array('Fibre', array_column( $payload['advice']['deficits'], 'nutrient')));

file_put_contents('tools/advisor/payload_good.json', json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

check('payload written for the panel test', is_file('tools/advisor/payload_good.json'));

// 4b) The menus, through their own mapping

$vocabulary = "Brokkoli R  (Rewe)  amounts: 25g | 50g | 100g
"
            . "Linsen R Bio  (Rewe)  amounts: 1/4 | 1/3 | 1/2
"
            . "Mandel R Bio  (Rewe)  amounts: 25g | 50g
"
            . "Olivenöl  amounts: 15ml | 30ml
"
            . "Knoblauch R  (Rewe)  amounts: 1
";

$recommended = [['food' => 'Brokkoli R'], ['food' => 'Linsen R Bio'], ['food' => 'Mandel R Bio']];

$menuNames = new ReflectionMethod('NutritionAdvisor', 'menuFoods');
$menuNames->setAccessible( true );

$names = $menuNames->invoke( null, $vocabulary );

check('vocabulary parses to names',
      $names === ['Brokkoli R', 'Linsen R Bio', 'Mandel R Bio', 'Olivenöl', 'Knoblauch R'],
      implode(', ', $names));

$out = NutritionAdvisor::menusFromAnswer(
  json_decode( file_get_contents('tools/advisor/menus_good.json'), true), $names, array_column( $recommended, 'food'));

$menus = $out['menus'];

check('usable menus kept',       count( $menus ) === 3, count( $menus ) . ' menus');
check('a menu without a title is dropped', ! in_array('', array_column( $menus, 'title')));
check('the drop is reported',    count( array_filter( $out['warnings'], fn($w) => str_contains($w, 'without a name'))) === 1,
      implode(' | ', $out['warnings']));

check('menu title',  $menus[0]['title'] === 'Linsenbowl mit Brokkoli', $menus[0]['title']);
check('ingredients', count( $menus[0]['ingredients']) === 4);
check('why and how', $menus[0]['why'] !== '' && $menus[0]['instructions'] !== '');

$roles = array_column( $menus[0]['ingredients'], 'role');

check('recommended foods are core', array_slice( $roles, 0, 2) === ['core', 'core'], implode(', ', $roles));
check('added foods are taste',      array_slice( $roles, 2)    === ['taste', 'taste'], implode(', ', $roles));

/* The role is checked against the analysis, not believed: a menu that labels a
   recommended food as an addition would hide what the menu is actually for */

$mislabelled = end( $menus );

check('a wrong role is corrected', $mislabelled['ingredients'][0]['role'] === 'core',
      $mislabelled['ingredients'][0]['role']);

// Same guarantee as the recommendations: a food the user does not own cannot be cooked

$second = $menus[1];

check('invented ingredient is dropped', count( $second['ingredients']) === 1,
      json_encode( array_column( $second['ingredients'], 'food')));
check('and reported', count( array_filter( $out['warnings'], fn($w) => str_contains($w, 'Gibt es nicht'))) === 1,
      implode(' | ', $out['warnings']));

$menuPayload = ['menus' => $menus];
file_put_contents('tools/advisor/menus_payload.json', json_encode( $menuPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

check('menu payload written for the panel test', is_file('tools/advisor/menus_payload.json'));

check('no usable menu is reported',
      NutritionAdvisor::menusFromAnswer(['menus' => []], $names, [])['warnings'] !== []);

// 5) A truncated or empty answer must not look like a good one

$result = NutritionAdvisor::fromAnswer([], []);

check('empty answer is reported', $result['warnings'] !== []);
check('empty answer has no content', $result['advice']['recommended'] === [] && $result['advice']['summary'] === '');

echo "\n  $pass passed, $fail failed\n";

exit( $fail ? 1 : 0);

?>
