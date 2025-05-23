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
  global $destDir; // Access the global destDir variable
  
  // Check if the current directory should be kept
  $normalized_dir = str_replace('\\', '/', $dir);
  
  foreach( $keep as $keep_item )
  {
    $keep_path = str_replace('\\', '/', "$destDir/$keep_item");
    
    // If this directory is or contains a path we want to keep
    if( $normalized_dir == $keep_path || 
        strpos($normalized_dir, $keep_path) === 0 || 
        strpos($keep_path, $normalized_dir) === 0 )
      return true;
  }
  
  $keepFld = false;
  
  foreach( scandir($dir) as $file)
  {
    if( in_array( $file, ['.', '..']))
      continue;
    
    $path = "$dir/$file";
    $normalized_path = str_replace('\\', '/', $path);
    
    if( is_dir($path))
    {
      $keepSub = false;
      
      // Check if this subdirectory should be kept
      foreach( $keep as $keep_item )
      {
        $keep_path = str_replace('\\', '/', "$destDir/$keep_item");
        
        if( $normalized_path == $keep_path || 
            strpos($normalized_path, $keep_path) === 0 || 
            strpos($keep_path, $normalized_path) === 0 )
        {
          $keepSub = true;
          break;
        }
      }
      
      // If not explicitly kept, check children
      if( ! $keepSub )
        $keepSub = clear_dest($path, $keep);
      
      $keepFld = $keepFld || $keepSub;
      
      if( ! $keepSub )
        rmdir($path);
    }
    elseif( is_file($path))
    {
      $keepFile = false;
      
      // Check if this file should be kept
      foreach( $keep as $keep_item )
      {
        $keep_path = str_replace('\\', '/', "$destDir/$keep_item");
        
        if( $normalized_path == $keep_path )
        {
          $keepFile = true;
          break;
        }
      }
      
      if( ! $keepFile )
        unlink($path);
    }
  }

  return $keepFld;
}

function deploy( $source, $dest, $keep )
{
  global $destDir; // Access the global destDir variable
  
  foreach( scandir($source) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;

    $dest_path = "$dest/$file";
    $source_path = "$source/$file";
    
    // Check if this file/folder should be kept (already exists in destination)
    $should_keep = false;
    
    if( file_exists($dest_path) )
    {
      $normalized_path = str_replace('\\', '/', $dest_path);
      
      foreach( $keep as $keep_item )
      {
        $keep_path = str_replace('\\', '/', "$destDir/$keep_item");
        
        // If this is a path we want to keep
        if( $normalized_path == $keep_path || 
            strpos($normalized_path, $keep_path) === 0 || 
            strpos($keep_path, $normalized_path) === 0 )
        {
          $should_keep = true;
          break;
        }
      }
    }
    
    // Skip if it's a kept file/folder
    if( $should_keep )
      continue;

    if( is_dir($source_path))
    {
      if( ! is_dir($dest_path))
        mkdir($dest_path, 0755, true);
      
      deploy($source_path, $dest_path, $keep);
    }
    else 
      copy($source_path, $dest_path);
  }
}

?>
