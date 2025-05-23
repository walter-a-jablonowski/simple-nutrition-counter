<?php

// TASK: move lib functions

// Helper

/*@

Returns true if a path should be kept based on the keep list

*/
function filter_str_ends( $path, $keep_list )  /*@*/
{
  $normalized_path = str_replace('\\', '/', $path); // Normalize path separators
  
  foreach( $keep_list as $keep_item )
  {
    $keep_item = str_replace('\\', '/', $keep_item); // Normalize keep item
    
    // Check if path ends with the keep item or contains it as a directory/file
    if( str_ends_with( $normalized_path, $keep_item ) || 
        strpos( $normalized_path, "/$keep_item" ) !== false || 
        strpos( $normalized_path, "/$keep_item/" ) !== false )
      return true;
  }
  
  return false;
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
