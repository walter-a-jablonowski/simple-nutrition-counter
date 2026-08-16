<?php

use Symfony\Component\Yaml\Yaml;

require_once 'lib/helper.php';

/*@

GetRangeNutrientsAjaxController

Nutrients tab over a time range (dropdown "This day", "7 days", ...). The browser
only holds the entries of the day it shows, so every longer range is aggregated
here over the day files.

The values are daily averages, sum of the range divided by the days it spans. That
keeps the per day targets of the nutrients tab valid: a week reads like a day

*/
trait GetRangeNutrientsAjaxController  /*@*/
{

  /*@

  getRangeNutrients()

  ARGS (request):
    range: range key, see rangeDates()
    date:  the date the app shows, the range is anchored to it

  RETURN: foods, one averaged entry per food name in the same shape the browser
          uses for a single day, plus the days the average is based on

  */
  public function getRangeNutrients( $request )  /*@*/
  {
    $range = $request['range'] ?? '';
    $dates = $this->rangeDates( $range, $request['date'] ?? date('Y-m-d'));

    if( $dates === null )
      return ['result' => 'error', 'data' => ['message' => "Unknown time range '$range'"] ];

    $dir   = 'data/users/' . config::get('defaultUser') . '/days';
    $foods = [];   // food name => summed nutrients over the whole range
    $daysWithData = 0;

    foreach( $dates as $date )
    {
      $file = "$dir/$date.tsv";

      if( ! is_file($file))
        continue;   // no entries logged that day, it still counts as a day (see below)

      $parsedFile = parse_data_file( file_get_contents($file));
      $entries    = parse_tsv( $parsedFile['data'], self::DAY_HEADERS);

      if( ! $entries )
        continue;

      $daysWithData++;

      foreach( $entries as $entry )
      {
        if( ! in_array( $entry['type'], self::FOOD_TYPES))
          continue;

        $nutrients = Yaml::parse( $entry['nutrients']);

        if( ! is_array($nutrients))
          continue;   // empty or broken column: the entry carries no nutrients

        $foods[$entry['food']] = $this->addNutrients( $foods[$entry['food']] ?? [], $nutrients);
      }
    }

    // Per day average. Averaging every food and adding them up afterwards gives the
    // same result as averaging the sum, so the browser can treat them like one day

    $days = count($dates);

    foreach( $foods as $name => $nutrients )
      $foods[$name] = [
        'food'      => $name,
        'nutrients' => $days ? $this->divideNutrients( $nutrients, $days) : $nutrients
      ];

    return ['result' => 'success', 'data' => [
      'foods'        => array_values($foods),
      'daysInRange'  => $days,
      'daysWithData' => $daysWithData
    ]];
  }


  /*@

  The dates a range covers, oldest first

  The running day is never part of a range: it is logged up to the current time
  only and would pull every average down. Ranges are anchored to the date the app
  shows, so looking at an older day gives the ranges around that day

  ARGS:
    range:  '7days', '30days', '90days', 'thisWeek', 'lastWeek', 'thisMonth'
    anchor: date the app shows (Y-m-d)

  RETURN: dates as Y-m-d, empty if the range has no completed day yet (e.g. "This
          week" on a monday), null if the range key is unknown

  */
  private function rangeDates( string $range, string $anchor ) : ?array
  {
    $anchorDay = new DateTimeImmutable($anchor);
    $end       = $anchor === date('Y-m-d') ? $anchorDay->modify('-1 day') : $anchorDay;

    if( preg_match('/^(\d+)days$/', $range, $match))
      $start = $end->modify('-' . ((int) $match[1] - 1) . ' days');

    elseif( $range === 'thisWeek' )
      $start = $anchorDay->modify('monday this week');

    elseif( $range === 'lastWeek' )
    {
      $start = $anchorDay->modify('monday last week');
      $end   = $start->modify('+6 days');   // a past week, always complete
    }
    elseif( $range === 'thisMonth' )
      $start = $anchorDay->modify('first day of this month');

    else
      return null;

    $dates = [];

    for( $day = $start; $day <= $end; $day = $day->modify('+1 day'))
      $dates[] = $day->format('Y-m-d');

    return $dates;
  }


  /*@

  Add one entry's nutrients onto a sum, keeping the structure the browser reads:
  groups like vit, min, amino plus the top level values fibre and sugar

  */
  private function addNutrients( array $sum, array $nutrients ) : array
  {
    foreach( $nutrients as $key => $value )
    {
      if( $key === 'amount' )   // portion label and weight, no nutrient
        continue;

      if( is_array($value))
        $sum[$key] = $this->addNutrients( $sum[$key] ?? [], $value);
      elseif( is_numeric($value))
        $sum[$key] = ($sum[$key] ?? 0) + $value;
    }

    return $sum;
  }


  // Same structure, every value divided by the days of the range

  private function divideNutrients( array $nutrients, int $days ) : array
  {
    foreach( $nutrients as $key => $value )
      $nutrients[$key] = is_array($value) ? $this->divideNutrients( $value, $days) : $value / $days;

    return $nutrients;
  }
}

?>
