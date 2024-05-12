<?php

function parse_tsv( $entriesTxt )
{
  $r = [];

  if( $entriesTxt )
  {
    $lines = explode("\n", $entriesTxt);

    foreach( $lines as $line )
    {
      // $line = preg_replace('/ {2,}/', ';', trim($line));
      $line  = preg_replace('/(?<=\S) {2,}(?=\S)/', ';', $line);  // added ignore spaces at bol (cause we are using str_pad() for amounts)
      $entry = explode(';', $line);
      $r[] = $entry;
    }
  }

  return $r;
}

/*@

- compatibility: YML needs more blanks (behind colon)
- empty array as js obj

*/
function dump_json( $array )  /**/
{
  // $r = json_encode( $array, JSON_FORCE_OBJECT);  // might also make num keys obj
  $r = str_replace('[]', '{}', json_encode( $array ));
  $r = preg_replace('/([:,])(?! )/', '$1 ', $r);

  return $r;
}

?>
