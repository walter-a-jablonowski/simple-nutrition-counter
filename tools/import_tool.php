<?php

// just cp text from table

$s = '

Durchschnittliche Nährwerte	pro 100 g
Energie	1.439 kJ
Energie	341 kcal
Fett	1,5 g
Fett, davon gesättigte Fettsäuren	0,3 g
Kohlenhydrate	50 g
Kohlenhydrate, davon Zucker	1,1 g
Eiweiß	25,5 g
Salz	0,01 g
';

// preg_replace('/\s+/', ' ', $originalString);
$lines = explode("\n", trim($s));
$vals['fibre'] = null;  // default (may be missing)

foreach( $lines as $line )
{
  $key = null;

  if( stripos( $line, 'kcal') !== false)
    $key = 'calories';
  elseif( stripos( $line, 'fett') !== false && stripos( $line, 'ttigt') === false)
    $key = 'fat'; 
  elseif( stripos( $line, 'ttigt') !== false)
    $key = 'saturatedFat';
  elseif( stripos( $line, 'kohle') !== false && stripos( $line, 'zucker') === false)
    $key = 'carbs';
  elseif( stripos( $line, 'zucker') !== false)
    $key = 'sugar';
  elseif( stripos( $line, 'bala') !== false)
    $key = 'fibre';
  elseif( stripos( $line, 'eiw') !== false)
    $key = 'amino';
  elseif( stripos( $line, 'salz') !== false)
    $key = 'salt';

  if( $key )
    $vals[$key] = str_replace(',', '.', preg_match("/\d+,\d+/", $line, $m) ? $m[0] : null);
}

echo "  calories:          $vals[calories]\n";
echo "  nutrients:\n";
echo "\n";
echo "    fat:             $vals[fat]\n";
echo "    saturatedFat:    $vals[saturatedFat]\n";
echo "    carbs:           $vals[carbs]\n";
echo "    sugar:           $vals[sugar]\n";
echo ! $vals['fibre'] ? '' :
     "    fibre:           $vals[fibre]\n";
echo "    amino:           $vals[amino]\n";
echo "    salt:            $vals[salt]\n";

?>
