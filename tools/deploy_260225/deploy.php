<?php

require 'config.php';
require 'lib.php';


$sourceDir = DEPLOY_SOURCE_DIR;
$destDir   = DEPLOY_DEST_DIR;
$backupDir = DEPLOY_BACKUP_DIR;

$backup = DEPLOY_BACKUP;
$keep   = DEPLOY_KEEP;

echo "Keep list (DEPLOY_KEEP):\n";
foreach( $keep as $item )
  echo "- $item\n";

echo "\nProceed? Type 'yes' to continue: ";
$confirm = strtolower( trim( fgets(STDIN)));
if( $confirm !== 'yes' )
{
  echo "No deploy\n";
  exit;
}


echo "Starting deployment...\n";

echo "Backing up files...\n";
backup( array_map( fn( $item ) => "$destDir/$item", $backup),
  $backupDir, $base = "$destDir/"
);

echo "Clearing destination directory...\n";
clear_dest( $destDir, $keep, $destDir );

echo "Deploying files...\n";
deploy( $sourceDir, $destDir, $keep, $destDir );

echo "\nDeployment completed successfully!\n";


function clear_dest( $dir, $keep, $baseDestDir )
{
  $keepFld = false;

  foreach( scandir($dir) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;

    if( strtolower($file) === 'desktop.ini' )
      continue;
    
    if( is_dir("$dir/$file") )
    {
      if( should_keep("$dir/$file", $keep, $baseDestDir) )
      {
        $keepFld = true;
        continue;
      }

      $keepSub = clear_dest("$dir/$file", $keep, $baseDestDir );
      $keepFld = $keepFld || $keepSub;

      if( $keepSub )
        continue;

      if( ! dir_is_empty("$dir/$file") )
      {
        if( dir_has_only_desktop_ini("$dir/$file") )
        {
          $keepFld = true;
          continue;
        }
      }

      echo "  Removing dir: $dir/$file\n";
      rmdir("$dir/$file");
    }
    elseif( is_file("$dir/$file") )
    {
      if( should_keep("$dir/$file", $keep, $baseDestDir) )
      {
        $keepFld = true;
        continue;
      }

      echo "  Removing file: $dir/$file\n";
      unlink("$dir/$file");
    }
  }

  return $keepFld;
}

function deploy( $source, $dest, $keep, $baseDestDir )
{
  foreach( scandir($source) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;

    if( strtolower($file) === 'desktop.ini' )
      continue;
    
    if( should_keep("$dest/$file", $keep, $baseDestDir) )
      continue;

    if( is_dir("$source/$file") )
    {
      if( ! is_dir("$dest/$file") )
        mkdir("$dest/$file", 0755, true);

      echo "  Deploying dir: $source/$file\n";
      deploy("$source/$file", "$dest/$file", $keep, $baseDestDir );
    }
    elseif( is_file("$source/$file") )
    {
      $destParent = dirname("$dest/$file");
      if( ! is_dir($destParent) )
        mkdir($destParent, 0755, true);

      echo "  Copying file: $source/$file\n";
      copy("$source/$file", "$dest/$file");
    }
  }
}

function should_keep( $path, $keep, $baseDestDir )
{
  $rel = get_relative_path( $path, $baseDestDir );
  if( $rel === '' )
    return true;

  foreach( $keep as $item )
  {
    $item = normalize_path($item);
    if( $rel === $item )
      return true;
    if( str_starts_with( $rel, "$item/"))
      return true;
  }

  return false;
}

function get_relative_path( $path, $baseDestDir )
{
  $path = normalize_path($path);
  $base = rtrim( normalize_path($baseDestDir), '/');

  if( $path === $base )
    return '';

  if( str_starts_with($path, "$base/") )
    return substr( $path, strlen($base) + 1 );

  return $path;
}

function normalize_path( $path )
{
  return str_replace('\\', '/', $path);
}

function dir_is_empty( $dir )
{
  foreach( scandir($dir) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;

    return false;
  }

  return true;
}

function dir_has_only_desktop_ini( $dir )
{
  foreach( scandir($dir) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;

    if( strtolower($file) !== 'desktop.ini' )
      return false;
  }

  return true;
}

?>
