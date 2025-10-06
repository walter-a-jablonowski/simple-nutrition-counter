<?php

$output = '';

foreach( $r as $vendor => $foods )  
{
  $output .= "\n$vendor\n\n";

  foreach( $foods as $entry )  // TASK: looks like formatting problem if Umlaut in name
    $output .= str_pad( $entry['food'], 26) . str_pad( $entry['price'], 5, ' ', STR_PAD_LEFT) . "  $entry[lastPriceUpd]\n";
}

file_put_contents( $out_file, $output );

?>
