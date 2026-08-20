<?php

require 'config.php';
require 'lib.php';


$sourceDir = DEPLOY_SOURCE_DIR;
$destDir   = DEPLOY_DEST_DIR;
$backupDir = DEPLOY_BACKUP_DIR;

$backup = DEPLOY_BACKUP;
$keep   = DEPLOY_KEEP;
$ignore = defined('DEPLOY_IGNORE') ? DEPLOY_IGNORE : [];

// Check the folders before anything is deleted: the destination is cleared before
// it is filled, so a wrong path would empty the installation and copy nothing.
// Paths are relative to this folder, deploy.php is meant to be run from here.

if( ! is_dir($sourceDir) )
{
  echo "\nSource folder not found: $sourceDir\n";
  echo "Run deploy.php from its own folder, or fix DEPLOY_SOURCE_DIR in config.php\n";
  exit(1);
}

if( ! is_dir($destDir) )
{
  echo "\nDestination folder not found: $destDir\n";
  echo "Create it first or fix DEPLOY_DEST_DIR in config.php\n";
  exit(1);
}

echo "\nIgnore list (DEPLOY_IGNORE):\n";
foreach( $ignore as $item )
  echo "- $item\n";

echo "\nKeep list (DEPLOY_KEEP):\n";
foreach( $keep as $item )
  echo "- $item\n";

echo "\nBackup list (DEPLOY_BACKUP):\n";
foreach( $backup as $item )
  echo "- $item\n";

echo "\nBackup folder (DEPLOY_BACKUP_DIR):\n";
echo "- $backupDir\n";

echo "\nDestination (DEPLOY_DEST_DIR):\n";
echo "- $destDir\n";

echo "\nProceed? Type 'yes' to continue: ";
$confirm = strtolower( trim( fgets(STDIN)));
if( $confirm !== 'yes' )
{
  echo "No deploy\n";
  exit;
}


echo "Starting deployment...\n";

try {

  echo "Backing up files...\n";
  if( $backupDir === '' )
    echo "  Skipped (DEPLOY_BACKUP_DIR is empty)\n";
  else
    backup( array_map( fn( $item ) => "$destDir/$item", $backup),
      $backupDir, "$destDir/"
    );

  echo "Clearing destination directory...\n";
  clear_dest( $destDir, $keep, $destDir );

  echo "Deploying files...\n";
  deploy( $sourceDir, $destDir, $keep, $destDir, $ignore, $sourceDir );
}
catch( Exception $e ) {
  echo "\nDeployment FAILED: {$e->getMessage()}\n";
  exit(1);
}

echo "\nDeployment successful!\n";


function clear_dest( $dir, $keep, $baseDestDir )
{
  if( ! is_dir($dir) )
    return false;

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
      fs_rmdir("$dir/$file");
    }
    elseif( is_file("$dir/$file") )
    {
      if( should_keep("$dir/$file", $keep, $baseDestDir) )
      {
        $keepFld = true;
        continue;
      }

      echo "  Removing file: $dir/$file\n";
      fs_unlink("$dir/$file");
    }
  }

  return $keepFld;
}

function deploy( $source, $dest, $keep, $baseDestDir, $ignore = [], $baseSourceDir = '' )
{
  foreach( scandir($source) as $file )
  {
    if( in_array( $file, ['.', '..']))
      continue;

    if( strtolower($file) === 'desktop.ini' )
      continue;

    if( should_ignore("$source/$file", $ignore, $baseSourceDir) )
      continue;

    if( should_keep("$dest/$file", $keep, $baseDestDir) )
      continue;

    if( is_dir("$source/$file") )
    {
      fs_mkdir("$dest/$file");

      echo "  Deploying dir: $source/$file\n";
      deploy("$source/$file", "$dest/$file", $keep, $baseDestDir, $ignore, $baseSourceDir );
    }
    elseif( is_file("$source/$file") )
    {
      fs_mkdir( dirname("$dest/$file"));

      echo "  Copying file: $source/$file\n";
      fs_copy("$source/$file", "$dest/$file");
    }
  }
}

function should_ignore( $path, $ignore, $baseSourceDir )
{
  $rel = get_relative_path( $path, $baseSourceDir );
  return path_matches_list( $rel, $ignore );
}

function should_keep( $path, $keep, $baseDestDir )
{
  $rel = get_relative_path( $path, $baseDestDir );
  if( $rel === '' )
    return true;

  return path_matches_list( $rel, $keep );
}

// Match a relative path against a list of paths/prefixes, case-insensitive (Windows FS)

function path_matches_list( $rel, $list )
{
  $rel = strtolower($rel);

  foreach( $list as $item )
  {
    $item = strtolower( normalize_path($item) );
    if( $rel === $item )
      return true;
    if( str_starts_with( $rel, "$item/"))
      return true;
  }

  return false;
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
