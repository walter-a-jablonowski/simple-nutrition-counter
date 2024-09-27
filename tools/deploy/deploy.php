<?php

// TASK: move lib functions, see below

$sourceDir = 'debug/source';
$dataDir   = 'days';
$destDir   = 'debug/dest';
$backupDir = 'debug/backup';

// $sourceDir = '..';
// $dataDir   = 'src/data/users/JaneDoe@example.com-24080101000000/days';
// $destDir   = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple-nutrition-counter (id-consump)';
// $backupDir = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple_running_SAV';

$keep = ['bootstrap-icons-1.11.3', '/days'];  // fil or fld, full dir may be used (last portion)

if( ! is_dir($sourceDir))
  die("Source dir missing: $sourceDir\n");

backup(["$sourceDir/file1.txt", "$sourceDir/$dataDir"], $backupDir, $base = "$sourceDir/");  // TASK: also backup config
clear_dest( $destDir, $keep);
// deploy( $sourceDir, $destDir, $keep);

echo 'Done';


function clear_dest( $dir, $keep )
{
  $keepFld = filter_str_ends( $dir, $keep );
  
  foreach( scandir($dir) as $file)
  {
    if( in_array( $file, ['.', '..']))
      continue;
  
    if( is_dir("$dir/$file"))
    {
      $keepSub = clear_dest("$dir/$file", $keep);
      $keepFld = $keepFld || $keepSub;
      
      if( ! $keepSub )
        rmdir("$dir/$file");
    }
    elseif( is_file("$dir/$file") && ! filter_str_ends("$dir/$file", $keep ))
      unlink("$dir/$file");  
  }

  return $keepFld;
}

function deploy( $source, $dest, $keep )
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
      
      deploy("$source/$file", "$dest/$file", $keep);
    }
    else 
      copy("$source/$file", "$dest/$file");
  }
}


// Helper

/*@

Returns true if a string ends with a string from a list of valid strings

*/
function filter_str_ends( $string, $valid_strings )  /*@*/  // TASK: maybe mov
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

function backup( $sources, $backupDir, $base )  // TASK: mov
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
      cp_recursive("$dir/$fil", "$destD ir/$sub/$fil");
    }
    else
      copy("$dir/$fil", "$destDir/$sub/$fil");
  }
}

?>
