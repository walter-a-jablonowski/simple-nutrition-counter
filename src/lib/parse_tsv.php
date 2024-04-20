<?php

function parse_tsv( $entriesTxt )
{
  $foodEntries = [];

  if( $entriesTxt )
  {
    $lines = explode("\n", $entriesTxt);

    foreach( $lines as $line )
    {
      // $line = trim($line);  // cause we are using str_pad() for amounts (looks like it's unneeded for some reason)
      $line = preg_replace('/ {2,}/', ';', trim($line));
      $entry = explode(';', $line);
      $foodEntries[] = $entry;
    }
  }

  return $foodEntries;
}

?>
