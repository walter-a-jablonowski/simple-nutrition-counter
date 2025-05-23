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
  // Simple check if directory should be kept
  foreach( $keep as $item )
    if( strpos($dir, $item) !== false )
      return true;
  
  $keepFld = false;
  
  foreach( scandir($dir) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;
    
    $path = "$dir/$file";
    
    if( is_dir($path) )
    {
      // Check if this subdirectory should be kept
      $keepSub = false;
      
      foreach( $keep as $item )
        if( strpos($path, $item) !== false )
        {
          $keepSub = true;
          break;
        }
      
      // If not explicitly kept, check children
      if( ! $keepSub )
        $keepSub = clear_dest($path, $keep);
      
      $keepFld = $keepFld || $keepSub;
      
      if( ! $keepSub )
        rmdir($path);
    }
    elseif( is_file($path) )
    {
      $keepFile = false;
      
      foreach( $keep as $item )
        if( strpos($path, $item) !== false )
        {
          $keepFile = true;
          break;
        }
      
      if( ! $keepFile )
        unlink($path);
    }
  }

  return $keepFld;
}

function deploy( $source, $dest, $keep )
{
  foreach( scandir($source) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;

    $dest_path = "$dest/$file";
    $source_path = "$source/$file";
    
    // Skip if file exists and should be kept
    if( file_exists($dest_path) )
    {
      $should_keep = false;
      
      foreach( $keep as $item )
        if( strpos($dest_path, $item) !== false )
        {
          $should_keep = true;
          break;
        }
      
      if( $should_keep )
        continue;
    }

    if( is_dir($source_path) )
    {
      if( ! is_dir($dest_path) )
        mkdir($dest_path, 0755, true);
      
      deploy($source_path, $dest_path, $keep);
    }
    else 
      copy($source_path, $dest_path);
  }
}

?>
