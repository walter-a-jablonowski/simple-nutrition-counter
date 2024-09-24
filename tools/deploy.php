<?php

$sourceDir   = '..';
$destDir     = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple-nutrition-counter (id-consump)';
$exclude = ['bootstrap-icons-1.11.3', 'days'];  // fil or fld
// TASK: maybe use larger portion of fil path to be able to be more precise

if( ! is_dir($sourceDir))
  die("Source dir missing: $sourceDir\n");

if( ! is_dir($destDir))
  mkdir($destDir, 0755, true);

// TASK: maybe we can make a backup of the last app as zip?
removeOldFiles( $destDir, $exclude);
copyNewFiles( $sourceDir, $destDir, $exclude);

echo "Success\n";


function removeOldFiles( $dir, $exclude )
{
  $excluded = false;
  
  foreach( scandir($dir) as $file)
  {
    if( in_array( $file, ['.', '..']))
      continue;
  
    $excl = in_array( $file, $exclude );

    if( is_dir("$dir/$file"))
      $excluded = removeOldFiles("$dir/$file", $exclude) || $excl;  // func first (short circuit)
    
    if( is_dir("$dir/$file") && ! $excl )
      rmdir("$dir/$file");
    elseif( is_file("$dir/$file") && ! $excl )
      unlink("$dir/$file");  
  }

  return $excluded;
}

function copyNewFiles( $source, $dest, $exclude )
{
  foreach( scandir($source) as $file)
  {
    if( in_array( $file, ['.', '..']))
      continue;

    if( in_array( $file, $exclude ))
      continue;

    if( is_dir("$source/$file"))
    {
      if( ! is_dir("$dest/$file"))
        mkdir("$dest/$file", 0755, true);
      
      copyNewFiles("$source/$file", "$dest/$file", $exclude);
    }
    else 
      copy("$source/$file", "$dest/$file");
  }
}

?>
