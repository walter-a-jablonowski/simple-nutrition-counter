<?php

/*

Exercises Publisher against a throwaway source + destination in the temp folder,
so the real installation folder is never touched. Run from src:

  php tools/test_publish_foods.php

*/

chdir( dirname(__DIR__));

require_once 'vendor/autoload.php';
require_once 'tools/publish_foods/Publisher.php';

use Symfony\Component\Yaml\Yaml;

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

function rm_tree( $dir )
{
  if( ! is_dir($dir))  return;

  foreach( scandir($dir) as $entry )
  {
    if( $entry === '.' || $entry === '..')  continue;
    is_dir("$dir/$entry") ? rm_tree("$dir/$entry") : unlink("$dir/$entry");
  }

  rmdir($dir);
}

// Layout: $root is the fake "repo root", the tool dir has to sit 3 levels below it

$root    = str_replace('\\', '/', sys_get_temp_dir()) . '/publish_foods_test';
$toolDir = "$root/src/tools/publish_foods";
$dest    = "$root/../publish_foods_test_dest";

rm_tree($root);
rm_tree( str_replace('/..', '', dirname($root)) . '/publish_foods_test_dest');

mkdir("$toolDir", 0777, true);
mkdir("$root/data/foods/Sub", 0777, true);
mkdir($dest, 0777, true);

file_put_contents("$root/data/foods/a.yml",     "a\n");
file_put_contents("$root/data/foods/b.yml",     "b\n");
file_put_contents("$root/data/foods/Sub/c.yml", "c\n");
file_put_contents("$root/data/foods/desktop.ini", "[.ShellClassInfo]\n");   // must be ignored
file_put_contents("$root/data/single.yml",      "single\n");

file_put_contents("$toolDir/config.yml", Yaml::dump([
  'destination' => $dest,
  'sources'     => ['data/foods', 'data/single.yml'],
  'ignore'      => ['desktop.ini', 'Thumbs.db', '.DS_Store']
], 4, 2));

$pub = new Publisher($toolDir);

// 1) First plan: everything is new, desktop.ini is not part of it

$plan = $pub->plan();
check('first plan finds all real files as new', count($plan['new']) === 4, count($plan['new']) . ' new');
check('ignored file is not published', ! in_array('data/foods/desktop.ini', $plan['new'], true));
check('nested file is found', in_array('data/foods/Sub/c.yml', $plan['new'], true));
check('single file source is found', in_array('data/single.yml', $plan['new'], true));

// 2) Run copies them and creates the sub folder

$result = $pub->run();
check('run copies every new file', $result['copied'] === 4, "copied $result[copied]");
check('no errors', $result['errors'] === [], implode('; ', $result['errors']));
check('nested file arrived', is_file("$dest/data/foods/Sub/c.yml"));
check('ignored file was not copied', ! is_file("$dest/data/foods/desktop.ini"));
check('manifest was written', is_file("$toolDir/published.json"));

// 3) Nothing changed -> nothing to do

$plan = $pub->plan();
check('second plan is empty', ! $plan['new'] && ! $plan['changed'] && ! $plan['deleted'], "unchanged {$plan['unchanged']}");
check('unchanged counts every file', $plan['unchanged'] === 4);

// 4) A content change is picked up, a pure time change is not

file_put_contents("$root/data/foods/a.yml", "a changed\n");
touch("$root/data/foods/b.yml", time() + 120);          // new time, same content

$plan = $pub->plan();
check('changed content detected', $plan['changed'] === ['data/foods/a.yml'], implode(',', $plan['changed']));
check('a new file time alone changes nothing', ! in_array('data/foods/b.yml', $plan['changed'], true));

$pub->run();
check('changed file was copied', file_get_contents("$dest/data/foods/a.yml") === "a changed\n");

// 5) Removing a source file reports it as obsolete, but does not delete by default

unlink("$root/data/foods/b.yml");

$plan = $pub->plan();
check('removed source file reported as obsolete', $plan['deleted'] === ['data/foods/b.yml'], implode(',', $plan['deleted']));

$pub->run( false );
check('obsolete file kept without --delete', is_file("$dest/data/foods/b.yml"));
check('obsolete file still reported afterwards', $pub->plan()['deleted'] === ['data/foods/b.yml']);

// 6) With deletion enabled it goes, and stops being reported

$result = $pub->run( true );
check('obsolete file deleted with --delete', ! is_file("$dest/data/foods/b.yml"));
check('deletion counted', $result['deleted'] === 1, "deleted $result[deleted]");
check('nothing left to do', $pub->plan()['deleted'] === []);

// 7) A destination file that matches is adopted without copying (fresh manifest)

unlink("$toolDir/published.json");
$plan = $pub->plan();
check('lost manifest does not re-copy identical files', ! $plan['new'] && ! $plan['changed'],
      count($plan['new']) . ' new, ' . count($plan['changed']) . ' changed');

// 8) Report lines

$lines = $pub->reportLines( $pub->plan());
check('report says everything is up to date', strpos($lines[0], 'Nothing to publish') === 0, $lines[0]);

rm_tree($root);
rm_tree($dest);

echo "\n$pass passed, $fail failed\n";
exit( $fail === 0 ? 0 : 1 );

?>
