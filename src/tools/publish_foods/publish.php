<?php

/*

CLI counterpart of the dev menu > Publish entry. Run from src:

  php tools/publish_foods/publish.php              # show what would change
  php tools/publish_foods/publish.php --run        # copy new + changed files
  php tools/publish_foods/publish.php --run --delete   # also remove obsolete files

*/

chdir( dirname(__DIR__, 2));   // tools/publish_foods -> src

require_once 'vendor/autoload.php';
require_once 'tools/publish_foods/Publisher.php';

$publisher = new Publisher( __DIR__ );

$run    = in_array('--run', $argv, true);
$delete = in_array('--delete', $argv, true);

if( ! $run )
{
  echo implode("\n", $publisher->reportLines( $publisher->plan(), $delete)), "\n";
  echo "\nDry run - add --run to publish.\n";
  exit(0);
}

$result = $publisher->run( $delete );

echo implode("\n", $publisher->reportLines( $result['plan'], $delete)), "\n\n";
echo "Copied $result[copied], deleted $result[deleted].\n";

foreach( $result['errors'] as $error )
  echo "  ERROR  $error\n";

exit( $result['errors'] ? 1 : 0);

?>
