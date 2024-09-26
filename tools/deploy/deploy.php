<?php

$sourceDir = 'debug/source';
$destDir   = 'debug/dest';
// $sourceDir = '..';
// $destDir   = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple-nutrition-counter (id-consump)';
$keep = ['bootstrap-icons-1.11.3', 'days'];  // fil or fld
// TASK: maybe use larger portion of fil path to be able to be more precise

if( ! is_dir($sourceDir))
  die("Source dir missing: $sourceDir\n");

if( ! is_dir($destDir))
  mkdir($destDir, 0755, true);

// TASK: maybe we can make a backup of the last app as zip?
removeOldFiles( $destDir, $keep);
// copyNewFiles( $sourceDir, $destDir, $keep);

echo 'Done';


function removeOldFiles( $dir, $keep )
{
  $keepFld = in_array( basename($dir), $keep );
  
  foreach( scandir($dir) as $file)
  {
    if( in_array( $file, ['.', '..']))
      continue;
    
    if( is_dir("$dir/$file"))
    {
      $keepFld = removeOldFiles("$dir/$file", $keep) || $keepFld;  // func first (short circuit)

      // $childKeepFld = removeOldFiles("$dir/$file", $keep);  // Check child directories
      // $keepFld = $childKeepFld || $keepFld;  // Update parent retention status
    }

    $keepFil = in_array( $file, $keep );

    if( is_dir("$dir/$file") && ! $keepFld )
      rmdir("$dir/$file");
    elseif( is_file("$dir/$file") && ! $keepFil )
      unlink("$dir/$file");  
  }

  return $keepFld;
}

function copyNewFiles( $source, $dest, $keep )
{
  foreach( scandir($source) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;

    if( in_array( $file, $keep ))
      continue;

    if( is_dir("$source/$file"))
    {
      if( ! is_dir("$dest/$file"))
        mkdir("$dest/$file", 0755, true);
      
      copyNewFiles("$source/$file", "$dest/$file", $keep);
    }
    else 
      copy("$source/$file", "$dest/$file");
  }
}

?>
