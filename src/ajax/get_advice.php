<?php

use Symfony\Component\Yaml\Yaml;

require_once 'lib/advisor/NutrientReport.php';
require_once 'lib/advisor/FoodRanking.php';
require_once 'lib/advisor/NutritionAdvisor.php';

/*@

GetAdviceAjaxController

Ajax: what the user's food is short of, what there is too much of, and which of their
own foods would help.

The work before the model is deterministic and free (NutrientReport, FoodRanking); the
model call takes half a minute and costs money, so its answer is cached per day and per
report. Logging another food changes the report and invalidates it by itself - no
expiry to get wrong.

See dev_info/Nutrition_Advisor_Plan.md

*/
trait GetAdviceAjaxController  /*@*/
{

  /*@

  getAdvice()

  ARGS (request):
    step:    'analyse' (menus follow later)
    date:    the date the app shows, the ranges are anchored to it
    refresh: true asks the model again even when a cached answer fits

  RETURN: { advice, report, warnings, cached }

  */
  public function getAdvice( $request )  /*@*/
  {
    if( ! config::get('advisor.enabled'))
      return ['result' => 'error', 'data' => ['message' => 'The nutrition advisor is switched off in the config.']];

    $step = $request['step'] ?? 'analyse';

    if( ! in_array( $step, ['analyse', 'menus']))
      return ['result' => 'error', 'data' => ['message' => "Unknown advisor step '$step'"]];

    $date = $request['date'] ?? date('Y-m-d');

    $this->loadNutritionModels();   // an ajax call gets a bare controller, see AppController

    try {
      [$reports, $selection] = $this->adviceInput( $date );
    }
    catch( Exception $e ) {
      return ['result' => 'error', 'data' => ['message' => $e->getMessage()]];
    }

    if( $step === 'menus' )
      return $this->adviceMenus( $date, $reports, $selection, ! empty( $request['refresh']));

    // Nothing to say, and no reason to pay for being told so

    if( ! $selection['deficits'] && ! $selection['excesses'] )
      return ['result' => 'error', 'data' => ['message' =>
        'There is not enough logged in this range to say anything useful yet.']];

    $key    = $this->adviceKey( $reports, $selection );
    $cached = empty( $request['refresh']) ? $this->readAdvice( $date, $key ) : null;

    if( $cached )
      return ['result' => 'success', 'data' => $cached + ['cached' => true]];

    set_time_limit( 180 );   // the model needs 20-40 s, php's default is 30

    try {
      $answer = NutritionAdvisor::analyse( $reports, $selection, $this->dietRules());
    }
    catch( Exception $e ) {
      return ['result' => 'error', 'data' => ['message' => $e->getMessage()]];
    }

    $data = [
      'advice'   => $answer['advice'],
      'warnings' => $answer['warnings'],
      'report'   => $this->reportForPanel( $reports, $selection )
    ];

    $this->writeAdvice( $date, $key, $data );

    return ['result' => 'success', 'data' => $data + ['cached' => false]];
  }


  /*@

  The menus, built on the analysis that is already cached for this day.

  It reads that analysis rather than running one: the two calls are separate so the
  creative half can be asked again without paying for the arithmetic half. Nothing to
  read means the panel asked out of order, which is worth saying rather than quietly
  starting a second analysis

  */
  private function adviceMenus( string $date, array $reports, array $selection, bool $refresh )  /*@*/
  {
    $key   = $this->adviceKey( $reports, $selection );
    $saved = $this->readAdvice( $date, $key );

    if( ! $saved )
      return ['result' => 'error', 'data' => ['message' => 'Ask for the advice first, the menus are built on it.']];

    if( ! $refresh && ! empty( $saved['menus']))
      return ['result' => 'success', 'data' => ['menus' => $saved['menus'], 'warnings' => [], 'cached' => true]];

    $recommended = $saved['advice']['recommended'] ?? [];

    if( ! $recommended )
      return ['result' => 'error', 'data' => ['message' => 'There are no recommended foods to build a menu from.']];

    set_time_limit( 180 );

    try {
      $answer = NutritionAdvisor::menus( $recommended, $this->foodVocabulary(),
                                         $selection['excesses'], $this->dietRules());
    }
    catch( Exception $e ) {
      return ['result' => 'error', 'data' => ['message' => $e->getMessage()]];
    }

    $saved['menus'] = $answer['menus'];

    $this->writeAdvice( $date, $key, $saved );

    return ['result' => 'success', 'data' => [
      'menus' => $answer['menus'], 'warnings' => $answer['warnings'], 'cached' => false]];
  }


  /*@

  Every food of the grid, one line each, as the menu step's vocabulary.

  The grid is the source, not the food folder: a record that is no longer laid out is
  one the user has retired, and a menu must not send them shopping for it. Same reason
  MainController.foodVocabulary() builds its list from the rendered grid

  */
  private function foodVocabulary() : string  /*@*/
  {
    $lines = [];

    foreach( $this->layoutView->all() as $name => $amounts )
    {
      $vendor = (string) ($this->combinedModel->get("$name.vendor") ?: '');
      $vendor = in_array( strtolower($vendor), ['none', 'multiple']) ? '' : $vendor;

      $line = $name;

      if( $vendor )
        $line .= "  ($vendor)";

      $offered = array_map( fn( $key ) => str_replace('_', '.', $key), array_keys( is_array($amounts) ? $amounts : []));

      if( $offered )
        $line .= '  amounts: ' . implode(' | ', $offered);

      if( $this->combinedModel->get("$name.category") === 'S' )
        $line .= '  [supplement]';

      $lines[] = $line;
    }

    return implode("
", $lines);
  }


  /*@

  The deterministic half: one report per configured range and the food shortlist.

  The shortlist is built from the **longest** range. Micronutrients need weeks - a
  single liver meal covers a month of vitamin A - so the long view decides what is
  worth recommending, while the short one is in the prompt for the model to notice a
  recent change. Ranges come out sorted, longest last

  */
  private function adviceInput( string $date ) : array  /*@*/
  {
    $days = config::get('advisor.ranges') ?: [7, 30];

    sort( $days );

    $report = new NutrientReport(
      $this->nutrientsView,
      $this->captions,
      config::get('defaultUser'),
      (int) (config::get('advisor.minCoverage') ?: 40));

    $reports = [];

    foreach( $days as $range )
    {
      $one = $report->forRange("{$range}days", $date );

      if( $one === null )
        throw new Exception("Unknown time range '{$range}days' in advisor.ranges");

      $reports[] = $one;
    }

    $ranking = new FoodRanking(
      $this->layoutView,
      $this->combinedModel,
      (int) (config::get('advisor.maxFoods') ?: 40));

    return [$reports, $ranking->select( end( $reports ))];
  }


  // The bundle's diet rules, markdown since they left html behind

  private function dietRules() : string
  {
    $bundle = Yaml::parse( file_get_contents('data/bundles/Default_' . User::current('id') . '/-this.yml')) ?: [];
    $parts  = [];

    foreach( ['framework', 'conceptMisc', 'goals', 'sampleMenu'] as $field )
      if( ! empty( $bundle[$field]))
        $parts[] = trim( $bundle[$field]);

    return implode("\n\n", $parts);
  }


  /* What the panel prints next to the model's words: the numbers, so the user can see
     what the advice was built on. The candidates carry their whole panel, which the
     panel has no use for - only the names it may offer to log */

  private function reportForPanel( array $reports, array $selection ) : array
  {
    $primary = end( $reports );

    return [
      'range'        => $primary['range'],
      'days'         => $primary['days'],
      'daysWithData' => $primary['daysWithData'],
      'coverage'     => $primary['coverage'],
      'macros'       => $primary['macros'],
      'deficits'     => array_map( fn($r) => ['nutrient' => $r['nutrient'], 'unit' => $r['unit'],
                                              'perDay' => $r['perDay'], 'ideal' => $r['ideal'],
                                              'coverage' => $r['coverage']], $selection['deficits']),
      'excesses'     => array_map( fn($r) => ['nutrient' => $r['nutrient'], 'unit' => $r['unit'],
                                              'perDay' => $r['perDay'], 'upper' => $r['upper']], $selection['excesses']),
      'candidates'   => array_map( fn($c) => ['food' => $c['food'], 'amount' => $c['amount']], $selection['candidates'])
    ];
  }


  /* Cache key: what the answer was built from. The report changes with every entry
     the user logs, so a stale answer can not survive a change to the day */

  private function adviceKey( array $reports, array $selection ) : string
  {
    return substr( sha1( json_encode([$reports, $selection])), 0, 16);
  }


  private function adviceFile( string $date ) : string
  {
    return 'data/users/' . config::get('defaultUser') . "/advice/$date.yml";
  }


  private function readAdvice( string $date, string $key ) : ?array
  {
    $file = $this->adviceFile( $date );

    if( ! is_file($file))
      return null;

    $saved = Yaml::parse( file_get_contents($file)) ?: [];

    return ($saved['key'] ?? '') === $key ? ($saved['data'] ?? null) : null;
  }


  // A cache that can not be written is no reason to throw the answer away

  private function writeAdvice( string $date, string $key, array $data ) : void
  {
    $file = $this->adviceFile( $date );
    $dir  = dirname( $file );

    if( ! is_dir($dir) && ! @mkdir( $dir, 0777, true))
      return;

    @file_put_contents( $file, Yaml::dump(
      ['key' => $key, 'written' => date('Y-m-d H:i:s'), 'data' => $data], 6, 2));
  }
}

?>
