<?php

function copyDir($src, $dst, $exclude = array()) {
  @mkdir($dst);
  $files = array_diff(scandir($src), array('.', '..'));

  foreach ($files as $file) {
    if (in_array($file, $exclude)) {
      continue;
    }
    $srcFile = "$src/$file";
    $dstFile = "$dst/$file";
    if (is_dir($srcFile)) {
      copyDir($srcFile, $dstFile, $exclude);
    } else {
      copy($srcFile, $dstFile);
    }
  }
}

function removeDir($dir, $exclude = array()) {
  $files = array_diff(scandir($dir), array('.', '..'));

  foreach ($files as $file) {
    if (in_array($file, $exclude)) {
      continue;
    }
    $filePath = "$dir/$file";
    is_dir($filePath) ? removeDir($filePath, $exclude) && rmdir($filePath) : unlink($filePath);
  }
}

function backupAndDeploy($srcDir, $installDir, $backupDir, $excludeBackup = array(), $excludeRemove = array()) {
  // Create a backup
  if (file_exists($installDir)) {
    @mkdir($backupDir, 0777, true);
    copyDir($installDir, $backupDir, $excludeBackup);
  }

  // Remove current source from the install directory except excluded files/folders
  removeDir($installDir, $excludeRemove);

  // Copy new source to the install directory except excluded files/folders
  copyDir($srcDir, $installDir, $excludeRemove);
}

// Define your directories
$srcDir = '/path/to/new/src';
$installDir = '/path/to/install';
$backupDir = '/path/to/backup';

// Define your exclusions
$excludeBackup = array('node_modules', '.git');
$excludeRemove = array('config.php', 'uploads');

// Perform the backup and deployment
backupAndDeploy($srcDir, $installDir, $backupDir, $excludeBackup, $excludeRemove);

echo "Deployment completed successfully.\n";

?>
