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

?>
