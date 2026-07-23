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

define('DEPLOY_SOURCE_DIR', '../../src');   // relative to this tool folder, deploy.php is run from here
define('DEPLOY_DEST_DIR',   'G:/Meine Ablage/80-dools/20_activity/simple-nutrition-counter (id-consump)/src');
define('DEPLOY_BACKUP_DIR', 'G:/Meine Ablage/80-dools/20_activity/simple-nutrition-counter_deploy-backup');

// No copy from the source, but still deleted at the destination: use this to get
// rid of something there. To leave it alone on both sides use DEPLOY_KEEP, which
// skips the clean up and the copy (listing it here as well changes nothing).

define('DEPLOY_IGNORE', [

]);

define('DEPLOY_BACKUP', [
  'config.yml',
  'data/users/JaneDoe@example.com-24080101000000/days'
]);

// Neither cleaned up nor copied: third party folders that only change on an
// upgrade, and the live data of the installation

define('DEPLOY_KEEP', [
  'config.yml',
  'lib/bootstrap-icons-1.11.3',
  'lib/bootstrap-5.3.3-dist',
  'lib/jquery-ui-1.13.3',
  'vendor',                                              // composer deps
  'data/users/JaneDoe@example.com-24080101000000/days'
]);

?>
