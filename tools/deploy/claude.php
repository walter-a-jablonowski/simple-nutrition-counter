<?php

function deployApp($srcDir, $installDir, $backupDir, $excludeFromBackup, $keepInInstall) {
    // Step 1: Backup current installation
    backupCurrentInstallation($installDir, $backupDir, $excludeFromBackup);

    // Step 2: Remove current source from install directory
    removeCurrentSource($installDir, $keepInInstall);

    // Step 3: Copy new source to install directory
    copyNewSource($srcDir, $installDir, $keepInInstall);
}

function backupCurrentInstallation($installDir, $backupDir, $excludeFromBackup) {
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $backupDestination = $backupDir . '/' . date('Y-m-d_H-i-s');
    mkdir($backupDestination, 0755, true);

    copyDirRecursive($installDir, $backupDestination, $excludeFromBackup);
}

function removeCurrentSource($installDir, $keepInInstall) {
    $items = scandir($installDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $installDir . '/' . $item;
        if (in_array($item, $keepInInstall)) {
            continue;
        }

        if (is_dir($path)) {
            removeCurrentSource($path, $keepInInstall);
            rmdir($path);
        } else {
            unlink($path);
        }
    }
}

function copyNewSource($srcDir, $installDir, $keepInInstall) {
    if (!file_exists($installDir)) {
        mkdir($installDir, 0755, true);
    }

    $items = scandir($srcDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $srcPath = $srcDir . '/' . $item;
        $destPath = $installDir . '/' . $item;

        if (in_array($item, $keepInInstall) && file_exists($destPath)) {
            continue;
        }

        if (is_dir($srcPath)) {
            copyNewSource($srcPath, $destPath, $keepInInstall);
        } else {
            copy($srcPath, $destPath);
        }
    }
}

function copyDirRecursive($src, $dest, $exclude) {
    $items = scandir($src);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $srcPath = $src . '/' . $item;
        $destPath = $dest . '/' . $item;

        if (in_array($item, $exclude)) {
            continue;
        }

        if (is_dir($srcPath)) {
            mkdir($destPath, 0755, true);
            copyDirRecursive($srcPath, $destPath, $exclude);
        } else {
            copy($srcPath, $destPath);
        }
    }
}

// Example usage
$srcDir = '/path/to/new/src';
$installDir = '/path/to/installation';
$backupDir = '/path/to/backups';
$excludeFromBackup = ['config.php', 'uploads'];
$keepInInstall = ['config.php', 'uploads'];

deployApp($srcDir, $installDir, $backupDir, $excludeFromBackup, $keepInInstall);

echo "Deployment completed successfully!";

?>
