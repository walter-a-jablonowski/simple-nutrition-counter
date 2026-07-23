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

// No copy from the source. Anything listed here must also be in DEPLOY_KEEP,
// otherwise the destination clean up deletes it and nothing puts it back.

define('DEPLOY_IGNORE', [
  'vendor'
]);

define('DEPLOY_BACKUP', [
  'config.yml',
  'data/users/JaneDoe@example.com-24080101000000/days'
]);

define('DEPLOY_KEEP', [
  'config.yml',
  'lib/bootstrap-icons-1.11.3',
  'lib/lib/bootstrap-5.3.3-dist',
  'lib/lib/lib/jquery-ui-1.13.3',
  'vendor',                                              // composer deps, see DEPLOY_IGNORE
  'data/users/JaneDoe@example.com-24080101000000/days'   // src/ prefix removed
]);

?>
