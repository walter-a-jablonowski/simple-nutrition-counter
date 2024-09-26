<?php

$sourceDir = 'debug/source';
$destDir   = 'debug/dest';
// $sourceDir = '..';
// $destDir   = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple-nutrition-counter (id-consump)';
$keep = ['bootstrap-icons-1.11.3', '/days'];  // fil or fld, full dir may be used (last portion)

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
  $keepFld = filter_str_ends( $dir, $keep );
  
  foreach( scandir($dir) as $file)
  {
    if( in_array( $file, ['.', '..']))
    continue;
  
    if( is_dir("$dir/$file"))
    {
      $keepSub = removeOldFiles("$dir/$file", $keep);
      $keepFld = $keepFld || $keepSub;
      
      if( ! $keepSub )
        rmdir("$dir/$file");
    }
    elseif( is_file("$dir/$file") && ! filter_str_ends("$dir/$file", $keep ))
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

    if( filter_str_ends("$source/$file", $keep ))
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

// TASK: move

function filter_str_ends( $string, $valid_strings )
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

?>
