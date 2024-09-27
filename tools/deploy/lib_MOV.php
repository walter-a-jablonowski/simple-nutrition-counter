<?php

// TASK: move lib functions

// Helper

/*@

Returns true if a string ends with a string from a list of valid strings

*/
function filter_str_ends( $string, $valid_strings )  /*@*/
{
  $keep = false;
  
  foreach( $valid_strings as $s )
    // if( strpos($string, $s) !== false && strpos($string, $s) + strlen($s) == strlen($string))
    if( str_ends_with( $string, $s))
    {
      $keep = true;
      break;
    }
  
  return $keep;
}


// Lib functions

function backup( $sources, $backupDir, $base )
{
  $destDir = "$backupDir/" . date('ymd_Hi');

  mkdir($destDir, 0755, true);
  
  foreach( $sources as $source )
  {
    $sub = str_replace( $base, '', $source);
  
    if( is_dir($source) )
    {
      mkdir("$destDir/$sub", 0755, true);
      cp_recursive( $source, $destDir, $base);
    }
    elseif( is_file($source) )
      copy( $source, "$destDir/$sub");
  }
}

function cp_recursive( $dir, $destDir, $base )
{
  foreach( scandir($dir) as $fil )
  {
    if( in_array( $fil, ['.', '..']))
      continue;

    $sub = str_replace( $base, '', $dir);

    if( is_dir("$dir/$fil") )
    {
      mkdir("$destDir/$sub/$fil", 0755, true);
      cp_recursive("$dir/$fil", "$destDir/$sub/$fil");
    }
    else
      copy("$dir/$fil", "$destDir/$sub/$fil");
  }
}

?>
