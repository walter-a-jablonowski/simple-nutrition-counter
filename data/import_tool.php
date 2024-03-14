<?php

// or use Chrome ext Copytables

$s = '

Energie	277 kcal
Fett	19 g
Fett, davon gesättigte Fettsäuren	8,7 g
Kohlenhydrate	1 g
Kohlenhydrate, davon Zucker	1 g
Ballaststoffe	1 g
Eiweiß	25 g
Salz	3,9 g
';

$s = trim($s);

$s = str_ireplace('Energie',                     '  calories:     ', $s );
$s = str_ireplace('davon gesättigte Fettsäuren', '  saturatedFat: ', $s );
$s = str_ireplace('Fett',                        '  fat:          ', $s );  // switched order needed
$s = str_ireplace('Kohlenhydrate',               '  carbs:        ', $s );
$s = str_ireplace('davon Zucker',                '  sugar:        ', $s );
$s = str_ireplace('Ballaststoffe',               '  fibre:        ', $s );
$s = str_ireplace('Eiweiß',                      '  amino:        ', $s );
$s = str_ireplace('Salz',                        '  salt:         ', $s );

// preg_replace('/\s+/', ' ', $originalString);

$s = str_replace(',', '.', $s );

$s = str_replace(' g',    '', $s );
$s = str_replace(' mg',   '', $s );
$s = str_replace(' kcal', '', $s );
$s = str_replace(' kj',   '', $s );

echo "  packaging:             \n"
   . "  weigth:           \n"
   . "$s\n"
   . "\n"
   . "  sources:          Web\n"
   . "  lastUpd:          2024-02-18\n";

?>
