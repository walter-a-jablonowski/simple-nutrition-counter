<?php

// DEBUG

// define('DEPLOY_SOURCE_DIR', 'debug/source');
// define('DEPLOY_DEST_DIR',   'debug/dest');
// define('DEPLOY_BACKUP_DIR', 'debug/backup');

// define('DEPLOY_BACKUP', [
//   "folder2/subfolder2",
//   "days/day1.txt"
// ]);

// define('DEPLOY_KEEP', [
//   "folder2/subfolder2",
//   "days/day1.txt"
// ]);

define('DEPLOY_SOURCE_DIR', '../src');
define('DEPLOY_DEST_DIR',   'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple-nutrition-counter (id-consump)');
define('DEPLOY_BACKUP_DIR', 'G:/Meine Ablage/80-dools/primary_dool/20_activity/simple-nutrition-counter_deploy-backup');

define('DEPLOY_IGNORE', [
  '.git',
  '.vscode'
]);

define('DEPLOY_BACKUP', [
  'config.yml',
  'data/users/JaneDoe@example.com-24080101000000/days'
]);

define('DEPLOY_KEEP', [
  'config.yml',
  'lib/bootstrap-icons-1.11.3',
  'src/data/users/JaneDoe@example.com-24080101000000/days'
]);

?>
