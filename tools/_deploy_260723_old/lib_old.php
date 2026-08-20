<?php

function backup( $sources, $backupDir, $base )
{
  $destDir = "$backupDir/" . date('ymd_Hi');

  mkdir($destDir, 0755, true);
  
  foreach( $sources as $source )
  {
    if( strtolower( basename($source)) === 'desktop.ini' )
      continue;

    $sub = str_replace( $base, '', $source);
  
    if( is_dir($source) )
    {
      mkdir("$destDir/$sub", 0755, true);
      cp_recursive( $source, $destDir, $base);
    }
    elseif( is_file($source) )
    {
      $parentDir = dirname("$destDir/$sub");
      if( ! is_dir($parentDir) )
        mkdir($parentDir, 0755, true);

      copy( $source, "$destDir/$sub");
    }
  }
}

function cp_recursive( $dir, $destDir, $base )
{
  foreach( scandir($dir) as $fil )
  {
    if( in_array( $fil, ['.', '..']))
      continue;

    if( strtolower($fil) === 'desktop.ini' )
      continue;

    $sub = str_replace( $base, '', $dir);

    if( is_dir("$dir/$fil") )
    {
      mkdir("$destDir/$sub/$fil", 0755, true);
      cp_recursive("$dir/$fil", $destDir, $base);
    }
    else
      copy("$dir/$fil", "$destDir/$sub/$fil");
  }
}

?>
