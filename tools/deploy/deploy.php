<?php

require 'lib_MOV.php';


$sourceDir = 'debug/source';
$dataDir   = 'days';
$destDir   = 'debug/dest';
$backupDir = 'debug/backup';

// $sourceDir = '..';
// $dataDir   = 'src/data/users/JaneDoe@example.com-24080101000000/days';
// $destDir   = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple-nutrition-counter (id-consump)';
// $backupDir = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple_running_SAV';

// TASK: also keep config
$keep = ['bootstrap-icons-1.11.3', '/days'];  // fil or fld, full dir may be used (last portion)

if( ! is_dir($sourceDir))
  die("Source dir missing: $sourceDir\n");

// TASK: also backup config
backup(["$sourceDir/file1.txt", "$sourceDir/$dataDir"], $backupDir, $base = "$sourceDir/");
clear_dest( $destDir, $keep);
deploy( $sourceDir, $destDir, $keep);

echo 'Done';


function clear_dest( $dir, $keep )
{
  $keepFld = filter_str_ends( $dir, $keep );

  // TASK: most likely just return
  if( $keepFld )
    return true;
  
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

?>
