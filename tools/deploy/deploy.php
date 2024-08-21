<?php

$sourceDir   = '/path/to/source';
$destDir     = '/path/to/dest';
$excludeDirs = ['vendor', 'node_modules', 'cache'];

if( ! is_dir($sourceDir))
  die("Source directory does not exist: $sourceDir\n");

if( ! is_dir($destDir))
  mkdir($destDir, 0755, true);

removeOldFiles($destDir, $excludeDirs);
copyNewFiles($sourceDir, $destDir, $excludeDirs);

echo "Deployment completed successfully.\n";


function removeOldFiles( $dir, $excludeDirs )
{
  $excluded = false;

  foreach( scandir($dir) as $file)
  {
    if( in_array( $file, ['.', '..']))
      continue;

    if( in_array("$source/$file", $excludeDirs))
    {
      $excluded = true;
      continue;
    }

    if( is_dir("$dir/$file"))
    {
      $excluded = removeOldFiles("$dir/$file", $excludeDirs) || $excluded;  // func first (short circuit)
      rmdir("$dir/$file");
    }
    elseif( ! $excluded )    // TASK: right ?
      unlink("$dir/$file");  
  }
}

function copyNewFiles( $source, $dest, $excludeDirs )
{
  foreach( scandir($source) as $file)
  {
    if( in_array( $file, ['.', '..']))
      continue;

    if( in_array("$source/$file", $excludeDirs))
      continue;

    if( is_dir("$source/$file"))
    {
      if( ! is_dir("$dest/$file"))
        mkdir("$dest/$file", 0755, true);
      
      copyNewFiles("$source/$file", "$dest/$file", $excludeDirs);
    }
    else 
      copy("$source/$file", "$dest/$file");
  }
}

?>
