<?php

function parse( $entriesTxt )
{
  $foodEntries = [];

  if( $entriesTxt )
  {
    $lines = explode("\n", $entriesTxt);

    foreach( $lines as $line)
    {
      $line = preg_replace('/ {2,}/', ';', trim($line));
      $entry = explode(';', $line);
      $foodEntries[] = $entry;
    }
  }

  return $foodEntries;
}

?>
