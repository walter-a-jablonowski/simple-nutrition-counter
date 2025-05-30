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


echo "Starting deployment...\n";

echo "Backing up files...\n";
backup( array_map( fn( $item ) => "$destDir/$item", $backup),
  $backupDir, $base = "$destDir/"
);

echo "Clearing destination directory...\n";
clear_dest( $destDir, $keep );

echo "Deploying files...\n";
deploy( $sourceDir, $destDir, $keep );

echo "\nDeployment completed successfully!\n";


function clear_dest( $dir, $keep )
{
  // Simple check if dir should be kept
  foreach( $keep as $item )
    if( strpos($dir, $item) !== false )
    {
      echo "  Keeping: $dir (matched $item)\n";
      return true;
    }
  
  $keepFld = false;
  
  foreach( scandir($dir) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;
    
    if( is_dir("$dir/$file") )
    {
      // Check if this subdirectory should be kept
      $keepSub = false;
      
      foreach( $keep as $item )
        if( strpos("$dir/$file", $item) !== false )
        {
          $keepSub = true;
          break;
        }
      
      // If not explicitly kept, check children
      if( ! $keepSub )
        $keepSub = clear_dest("$dir/$file", $keep);
      
      $keepFld = $keepFld || $keepSub;
      
      if( ! $keepSub )
      {
        echo "  Removing dir: $dir/$file\n";
        rmdir("$dir/$file");
      }
    }
    elseif( is_file("$dir/$file") )
    {
      $keepFile = false;
      
      foreach( $keep as $item )
        if( strpos("$dir/$file", $item) !== false )
        {
          $keepFile = true;
          echo "  Keeping file: $dir/$file (matched $item)\n";
          break;
        }
      
      // if( ! $keepFile )
      // {
      //   echo "  Removing file: $dir/$file\n";
      //   unlink("$dir/$file");
      // }
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
    
    // Skip if file exists and should be kept
    if( file_exists("$dest/$file") )
    {
      $should_keep = false;
      
      foreach( $keep as $item )
        if( strpos("$dest/$file", $item) !== false )
        {
          $should_keep = true;
          break;
        }
      
      if( $should_keep )
        continue;
    }

    if( is_dir("$source/$file") )
    {
      if( ! is_dir("$dest/$file") )
        mkdir("$dest/$file", 0755, true);
      
      echo "  Deploying dir: $source/$file\n";
      deploy("$source/$file", "$dest/$file", $keep);
    }
    // else 
    // {
    //   echo "  Copying file: $source/$file\n";
    //   copy("$source/$file", "$dest/$file");
    // }
  }
}

?>
