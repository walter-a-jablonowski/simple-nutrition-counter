<?php

// TASK: make a tool "add_unpricise" and upd the found files if header missing

// Day files with line counts below average
// - Scans: src/data/users/JaneDoe@example.com-24080101000000/days
// - Ignores headers (separated from data by a blank line)
// - Flags files whose data line count is a certain amount below the average
// - Usable via CLI and browser
//
// CLI usage examples:
//   php verify_days.php
//   php verify_days.php --threshold=0.3
//   php verify_days.php --daysDir="c:/path/to/days"
//
// Browser usage examples:
//   verify_days.php
//   verify_days.php?threshold=0.3
//   verify_days.php?daysDir=C:/path/to/days

// Include helpers
require_once __DIR__ . '/../../lib/helper.php';

// Resolve defaults
$defaultDaysDir = realpath(__DIR__ . '/../../data/users/JaneDoe@example.com-24080101000000/days');
$threshold      = 0.30;  // 30% below average by default
$daysDir        = $defaultDaysDir;

// Parse CLI options
if( php_sapi_name() === 'cli' )
{
  foreach( $argv as $arg )
  {
    if( strpos($arg, '--threshold=') === 0 )
    {
      $val = substr($arg, strlen('--threshold='));
      if( is_numeric($val) )
        $threshold = max(0, min(0.95, floatval($val)));
    }
    elseif( strpos($arg, '--daysDir=') === 0 )
    {
      $val = substr($arg, strlen('--daysDir='));
      if( $val )
      {
        $resolved = realpath($val);
        if( $resolved )  $daysDir = $resolved;
      }
    }
    elseif( in_array($arg, ['--help', '-h']) )
    {
      fwrite(STDOUT, "Usage: php verify_days.php [--threshold=0.3] [--daysDir=PATH]\n");
      exit(0);
    }
  }
}
else  // Browser
{
  if( isset($_GET['threshold']) && is_numeric($_GET['threshold']) )
    $threshold = max(0, min(0.95, floatval($_GET['threshold'])));

  if( isset($_GET['daysDir']) && $_GET['daysDir'] )
  {
    $resolved = realpath($_GET['daysDir']);
    if( $resolved )  $daysDir = $resolved;
  }
}

// Validate dir
if( ! $daysDir || ! is_dir($daysDir) )
{
  $msg = "Days dir missing: " . ($daysDir ?: '(empty)');
  if( php_sapi_name() === 'cli' )
  {
    fwrite(STDERR, $msg . "\n");
    exit(1);
  }
  else
  {
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
    exit;
  }
}

// Collect TSV files
$files = array_values(array_filter(scandir($daysDir), function($f) use ($daysDir) {
  if( $f === '.' || $f === '..' ) return false;
  $path = $daysDir . DIRECTORY_SEPARATOR . $f;
  if( ! is_file($path) ) return false;
  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  return in_array($ext, ['tsv', 'txt']);  // consider .tsv and .txt
}));

$stats = [];
$sumLinesAll = 0;
$nonEmptyFiles = 0;

foreach( $files as $f )
{
  $path = $daysDir . DIRECTORY_SEPARATOR . $f;
  $content = file_get_contents($path);
  if( $content === false )  continue;

  $parsed = parse_data_file($content);  // from helper.php; returns ['headers'=>[], 'data'=>string]

  // Count non-empty data lines only
  $dataText  = $parsed['data'];
  $dataLines = $dataText === '' ? [] : array_map('rtrim', explode("\n", $dataText));
  $count     = 0;

  foreach( $dataLines as $line )
    if( trim($line) !== '' )
      $count++;

  $stats[] = [
    'file'  => $f,
    'path'  => $path,
    'count' => $count
  ];

  if( $count > 0 )
  {
    $sumLinesAll += $count;
    $nonEmptyFiles++;
  }
}

$avg = $nonEmptyFiles > 0 ? $sumLinesAll / $nonEmptyFiles : 0;
$cutoff = $avg * (1 - $threshold);

$flagged = array_values(array_filter($stats, function($s) use ($cutoff) {
  // Flag strictly below cutoff, only if average is meaningful (>0)
  return $cutoff > 0 && $s['count'] < $cutoff;
}));

// Output
if( php_sapi_name() === 'cli' )
{
  fwrite(STDOUT, "Scan dir: {$daysDir}\n");
  fwrite(STDOUT, sprintf("Files: %d, Non-empty: %d, Avg(data lines): %.2f, Threshold: %.0f%% below, Cutoff: %.2f\n",
    count($stats), $nonEmptyFiles, $avg, $threshold * 100, $cutoff));
  fwrite(STDOUT, "\nFlagged files (below cutoff):\n");

  if( empty($flagged) )
    fwrite(STDOUT, "  None\n");
  else
  {
    foreach( $flagged as $s )
      fwrite(STDOUT, sprintf("  %-30s  %5d lines\n", $s['file'], $s['count']));
  }
}
else
{
  header('Content-Type: text/html; charset=utf-8');
  ?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8">
    <title>Verify Day Files</title>
    <style>
      body { font-family: Arial, sans-serif; margin: 20px; }
      table { border-collapse: collapse; width: 100%; }
      th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
      th { background: #f3f3f3; }
      tbody tr.flag td { background: #fff3cd; }
      .muted { color: #666; }
      .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
      .small { font-size: 0.95em; }
      .right { text-align: right; }
    </style>
  </head>
  <body>
    <h2>Verify Day Files</h2>

    <p class="small">
      <span class="mono">Days dir:</span> <?= htmlspecialchars($daysDir) ?>
      <br>
      Files: <?= count($stats) ?>,
      Non-empty: <?= $nonEmptyFiles ?>,
      Avg(data lines): <?= number_format($avg, 2) ?>,
      Threshold: <?= number_format($threshold * 100, 0) ?>% below,
      Cutoff: <?= number_format($cutoff, 2) ?>
    </p>

    <form method="get" class="small">
      <label>Threshold (0..0.95): <input name="threshold" value="<?= htmlspecialchars($threshold) ?>" size="6"></label>
      <label style="margin-left:10px;">Days dir: <input name="daysDir" value="<?= htmlspecialchars($daysDir) ?>" size="60"></label>
      <button type="submit">Apply</button>
    </form>

    <h3>Flagged files (below cutoff)</h3>
    <?php if( empty($flagged) ): ?>
      <p class="muted">None</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>File</th>
            <th class="right">Data lines</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach( $flagged as $s ): ?>
            <tr class="flag">
              <td><?= htmlspecialchars($s['file']) ?></td>
              <td class="right"><?= (int)$s['count'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </body>
  </html>
  <?php
}

?>
