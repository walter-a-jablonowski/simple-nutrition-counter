<?php

chdir('../../..');

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';

// SETTINGS (adjust if needed)
$user_id    = 'JaneDoe@example.com-24080101000000';
$foods_dir  = "data/bundles/Default_{$user_id}/foods";
$import_yml = 'tools/bulk_price_upd/data/import.yml';

// Helpers
function nrm($v)
{
  $s = trim((string)$v);
  if( $s === '' ) return '';
  $s = str_replace(["\xC2\xA0", ' '], '', $s);
  $s = str_replace(',', '.', $s);
  // keep only last dot as decimal separator
  $parts = explode('.', $s);
  if( count($parts) > 2 ) $s = implode('', array_slice($parts, 0, -1)) . '.' . end($parts);
  return $s;
}

function to_num_or_null($v)
{
  $s = nrm($v);
  return $s === '' || !is_numeric($s) ? null : (float)$s;
}

function scan_foods($dir)
{
  $foods = [];
  $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
  foreach( $rii as $file )
  {
    if( $file->isDir() ) continue;
    if( pathinfo($file->getFilename(), PATHINFO_EXTENSION) !== 'yml') continue;
    $fn = $file->getFilename();
    $path = $file->getPathname();

    // Skip template/this files in the top-level scan branch; we'll still capture -this.yml as actual records
    try {
      $data = Yaml::parseFile($path);
      if( ! is_array($data)) continue;
    }
    catch( ParseException $e ) { continue; }

    if( str_ends_with($fn, '-this.yml') )
    {
      $name = basename($file->getPath());
      $foods[$name] = $data;
    }
    else
    {
      // Skip special files that start with underscore
      if( substr($fn, 0, 1) === '_' ) continue;
      // Plain file food
      $name = pathinfo($fn, PATHINFO_FILENAME);
      $foods[$name] = $data;
    }
  }
  return $foods;
}

function looks_unlikely(?float $cur, ?float $new) : array
{
  $flags = [];
  if( $new === null ) return $flags; // nothing to verify
  if( $new <= 0 ) { $flags[] = 'non-positive new price'; return $flags; }
  if( $cur === null ) { $flags[] = 'no current price'; return $flags; }

  $abs = abs($new - $cur);
  $rel = $cur > 0 ? $abs / $cur : INF;

  if( $abs >= 2.00 ) $flags[] = 'abs jump ≥ 2.00€';
  if( $rel >= 0.50 ) $flags[] = 'rel jump ≥ 50%';
  if( $new >= 2.0 * $cur ) $flags[] = '>= 2× current';
  if( $new <= 0.5 * $cur ) $flags[] = '<= 0.5× current';

  return $flags;
}

function format2(?float $v) : string { return $v === null ? 'n/a' : number_format($v, 2, '.', ''); }

// Load import map
$import_map = [];
if( ! file_exists($import_yml) )
{
  $msg = "import.yml missing: {$import_yml}";
  if( PHP_SAPI === 'cli') { fwrite(STDERR, $msg."\n"); exit(1); }
  header('Content-Type: text/plain; charset=utf-8');
  echo $msg; exit;
}
try {
  $parsed = Yaml::parseFile($import_yml);
  if( is_array($parsed)) $import_map = $parsed;
}
catch( Throwable $e ) {
  $msg = 'Error parsing import.yml: ' . $e->getMessage();
  if( PHP_SAPI === 'cli') { fwrite(STDERR, $msg."\n"); exit(2); }
  header('Content-Type: text/plain; charset=utf-8');
  echo $msg; exit;
}

// Load foods
$foods = [];
if( ! is_dir($foods_dir))
{
  $msg = 'Foods directory missing: ' . $foods_dir;
  if( PHP_SAPI === 'cli') { fwrite(STDERR, $msg."\n"); exit(3); }
  header('Content-Type: text/plain; charset=utf-8'); echo $msg; exit;
}
$foods = scan_foods($foods_dir);

$rows = [];
$flagged = [];
foreach( $import_map as $name => $entry )
{
  if( ! is_array($entry)) continue;
  $cur = $foods[$name] ?? null;

  $cur_price  = to_num_or_null($cur['price']     ?? null);
  $cur_deal   = to_num_or_null($cur['dealPrice'] ?? null);

  $new_price  = to_num_or_null($entry['price']     ?? null);
  $new_deal   = to_num_or_null($entry['dealPrice'] ?? null);

  // Compare independently: price vs price, dealPrice vs dealPrice
  $flags_price = $new_price !== null ? looks_unlikely($cur_price, $new_price) : [];
  $flags_deal  = $new_deal  !== null ? looks_unlikely($cur_deal,  $new_deal)  : [];
  // Merge for quick counting
  $flags = [];
  if( $flags_price ) $flags[] = 'price: ' . implode(', ', $flags_price);
  if( $flags_deal )  $flags[] = 'deal: '  . implode(', ', $flags_deal);

  $row = [
    'name'      => $name,
    'cur_price' => $cur_price,
    'cur_deal'  => $cur_deal,
    'new_price' => $new_price,
    'new_deal'  => $new_deal,
    'flags'     => $flags,
    'flags_price' => $flags_price,
    'flags_deal'  => $flags_deal,
  ];
  $rows[] = $row;
  if( $flags ) $flagged[] = $row;
}

// Output
if( PHP_SAPI === 'cli')
{
  $total = count($rows);
  $bad   = count($flagged);
  echo "Verify import: {$bad} flagged out of {$total}\n";
  foreach( $flagged as $r )
  {
    $parts = [];
    if( !empty($r['flags_price'])) $parts[] = 'price: ' . implode(', ', $r['flags_price']) . ' ('.format2($r['cur_price']).' → '.format2($r['new_price']).')';
    if( !empty($r['flags_deal']))  $parts[] = 'deal: '  . implode(', ', $r['flags_deal'])  . ' ('.format2($r['cur_deal']).' → '.format2($r['new_deal']).')';
    echo "- {$r['name']}  " . implode(' | ', $parts) . "\n";
  }
  exit( $bad ? 10 : 0 );
}

// Browser HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Verify import.yml</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; padding:16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
    th { background: #f6f6f6; }
    tr.flagged { background: #fff7f7; }
    .flags { color: #a00; font-weight: 600; }
    .num { text-align: right; font-variant-numeric: tabular-nums; }
  </style>
</head>
<body>
  <h3>Verify import.yml</h3>
  <p>Source: <code><?= htmlspecialchars($import_yml) ?></code></p>
  <p>Foods dir: <code><?= htmlspecialchars($foods_dir) ?></code></p>

  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th class="num">Current price</th>
        <th class="num">Current deal</th>
        <th class="num">New price</th>
        <th class="num">New deal</th>
        <th>Flags</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $rows as $r ): $isFlag = !empty($r['flags']); ?>
        <tr class="<?= $isFlag ? 'flagged' : '' ?>">
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td class="num"><?= htmlspecialchars(format2($r['cur_price'])) ?></td>
          <td class="num"><?= htmlspecialchars(format2($r['cur_deal'])) ?></td>
          <td class="num"><?= htmlspecialchars(format2($r['new_price'])) ?></td>
          <td class="num"><?= htmlspecialchars(format2($r['new_deal'])) ?></td>
          <td class="flags"><?= $isFlag ? htmlspecialchars(implode(', ', $r['flags'])) : '' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p><strong><?= count($flagged) ?></strong> flagged out of <strong><?= count($rows) ?></strong>.</p>
</body>
</html>
