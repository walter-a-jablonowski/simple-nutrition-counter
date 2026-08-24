<?php

use Symfony\Component\Yaml\Yaml;

require_once 'lib/helper.php';
require_once 'models/functions.php';   // range_dates()

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
    range: range key, see range_dates() in models/functions.php
    date:  the date the app shows, the range is anchored to it

  RETURN: foods, one averaged entry per food name in the same shape the browser
          uses for a single day, plus the days the average is based on

  */
  public function getRangeNutrients( $request )  /*@*/
  {
    $range = $request['range'] ?? '';
    $dates = range_dates( $range, $request['date'] ?? date('Y-m-d'));

    if( $dates === null )
      return ['result' => 'error', 'data' => ['message' => "Unknown time range '$range'"] ];

    $dir   = 'data/users/' . config::get('defaultUser') . '/days';
    $foods = [];   // food name => summed nutrients over the whole range
    $daysWithData = 0;

    foreach( $dates as $date )
    {
      $entries = read_day_file("$dir/$date.tsv")['entries'];

      if( ! $entries )
        continue;   // nothing logged that day, it still counts as a day (see below)

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
