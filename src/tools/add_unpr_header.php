<?php

// Batch add headers (unprecise / unpreciseTime) to day TSV files within a date range

chdir('..');

require_once 'lib/helper.php';

// Config

$from    = '2025-10-30';  // use in console
$to      = '2026-01-28';
$addUnp  = true;          // add unprecise: true
$addUnpt = true;          // add unpreciseTime: true

$userId  = 'JaneDoe@example.com-24080101000000';
$daysDir = "data/users/$userId/days";

function bad_request( $msg )
{
  http_response_code(400);
  header('Content-Type: text/plain; charset=utf-8');
  echo $msg;
  exit;
}

// Validate
if( ! $from || ! $to )              bad_request('Missing required config: set $from and $to (YYYY-MM-DD) in the Config block');
if( ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) )  bad_request('Invalid from date format');
if( ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) )    bad_request('Invalid to date format');
if( ! $addUnp && ! $addUnpt )       bad_request('Nothing to do: set $addUnp and/or $addUnpt to true in the Config block');
if( ! is_dir($daysDir) )            bad_request("Days directory not found: $daysDir");

$fromStr = $from;  // filenames are YYYY-MM-DD.tsv; string compare is OK when formats are fixed
$toStr   = $to;

if( $fromStr > $toStr )
  bad_request("Invalid date range: from ($fromStr) is after to ($toStr)");

$processed = 0;
$updated   = 0;
$errors    = [];

header('Content-Type: text/plain; charset=utf-8');

foreach( scandir($daysDir) as $file )
{
  if( $file === '.' || $file === '..' )  continue;
  if( ! preg_match('/^(\d{4}-\d{2}-\d{2})\.tsv$/', $file, $m) )  continue;
  $d = $m[1];
  if( $d < $fromStr || $d > $toStr )  continue;  // outside range

  $processed++;
  $path = "$daysDir/$file";
  $content = @file_get_contents($path);
  if( $content === false )
  {
    $errors[] = "Failed to read $file";
    continue;
  }

  $parsed   = parse_data_file($content);
  $headers  = $parsed['headers'];
  $dataBody = $parsed['data'];

  $changed = false;
  if( $addUnp && empty($headers['unprecise']) )
  {
    $headers['unprecise'] = true;
    $changed = true;
  }
  if( $addUnpt && empty($headers['unpreciseTime']) )
  {
    $headers['unpreciseTime'] = true;
    $changed = true;
  }

  if( ! $changed )  continue; // nothing to write

  $newContent = format_headers_to_string($headers) . $dataBody . "\n";  // ensure trailing newline
  if( @file_put_contents($path, $newContent) === false )
  {
    $errors[] = "Failed to write $file";
    continue;
  }

  echo "- updated: $file\n";
  $updated++;
}

echo "Processed: $processed\n";
echo "Updated:   $updated\n";
if( $errors )
{
  echo "Errors:    " . count($errors) . "\n";
  foreach( $errors as $e )  echo "- $e\n";
}
