<?php

require 'lib_MOV.php';


// Will copy /src only

// DEBUG

// $sourceDir = 'debug/source';
// $destDir   = 'debug/dest';
// $backupDir = 'debug/backup';

// $backup = [
//
// ];

// $keep = [
//
// ];

$sourceDir = '../src';
$destDir   = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple-nutrition-counter (id-consump)';
$backupDir = 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple_running_backup';

$backup = [
  'config.yml',
  'data/users/JaneDoe@example.com-24080101000000/days'
];

$keep = [
  'config.yml',
  'lib/bootstrap-icons-1.11.3',
  'src/data/users/JaneDoe@example.com-24080101000000/days'
];

if( ! is_dir($sourceDir))
  die("Source dir missing: $sourceDir\n");

backup( array_map( fn( $item ) => "$destDir/$item", $backup),
  $backupDir, $base = "$destDir/"
);

clear_dest( $destDir, $keep );
deploy( $sourceDir, $destDir, $keep );

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
