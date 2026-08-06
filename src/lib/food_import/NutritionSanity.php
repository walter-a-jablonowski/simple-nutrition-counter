<?php

/*

Plausibility checks for a food record: are these numbers possible at all?

FoodValidator answers a different question — whether the required fields are
present — so this lives on its own. These checks never block anything, they are
hints shown next to a form the user reviews before saving.

They mainly catch what goes wrong when numbers are read off a packaging photo:
a misplaced decimal, a per-portion column taken for a per-100g one, a row read
one line off, or an invented value that does not add up.

*/
class NutritionSanity
{

  const ENERGY_TOLERANCE = 0.25;   // Atwater estimate vs the printed kcal


  // Returns a list of warnings (empty array = nothing suspicious)

  public static function warnings( array $food ) : array
  {
    $out = [];
    $nv  = $food['nutritionalValues'] ?? [];

    $fat      = $nv['fat']          ?? null;
    $satFat   = $nv['saturatedFat'] ?? null;
    $carbs    = $nv['carbs']        ?? null;
    $sugar    = $nv['sugar']        ?? null;
    $fibre    = $nv['fibre']        ?? null;
    $amino    = $nv['amino']        ?? null;
    $salt     = $nv['salt']         ?? null;
    $calories = $food['calories']   ?? null;

    $sum = (float) $fat + (float) $carbs + (float) $amino + (float) $fibre + (float) $salt;

    if( $sum > 100 )
    {
      $total = round( $sum );
      $out[] = "Fat, carbs, protein and fibre add up to $total g per 100 g — they can not all be per 100 g.";
    }

    // Atwater: 9 kcal per g of fat, 4 per carbs and protein, 2 per fibre. A printed
    // kcal value that disagrees means one of the two readings is wrong

    if( $calories > 0 && $fat !== null && $carbs !== null && $amino !== null )
    {
      $estimate = 9 * $fat + 4 * $carbs + 4 * $amino + 2 * (float) $fibre;

      if( abs( $estimate - $calories ) / $calories > self::ENERGY_TOLERANCE )
      {
        $fromNutrients = round( $estimate );
        $printed       = round( $calories );
        $out[]         = "The nutrients add up to about $fromNutrients kcal but the label says $printed — one of them was misread.";
      }
    }

    if( $sugar !== null && $carbs !== null && $sugar > $carbs )
      $out[] = 'Sugar is higher than carbs — a row was probably read one line off.';

    if( $satFat !== null && $fat !== null && $satFat > $fat )
      $out[] = 'Saturated fat is higher than total fat — a row was probably read one line off.';

    if( $salt > 10 )
      $out[] = "Salt is $salt g per 100 g — that is probably sodium in mg, not salt in g.";

    if( $calories > 900 )
      $out[] = 'More than 900 kcal per 100 g is impossible — pure fat is 900.';

    return $out;
  }
}

?>
