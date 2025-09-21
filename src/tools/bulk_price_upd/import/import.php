<?php

use Symfony\Component\Yaml\Yaml;

require_once '../../../vendor/autoload.php';
require_once '../lib/variant_helper.php';

// SETTINGS
$user_id    = 'JaneDoe@example.com-24080101000000';
$foods_dir  = "../../../data/bundles/Default_$user_id/foods";
$import_yml = '../data/import.yml';

header('Content-Type: text/html; charset=utf-8');

$errors = [];
$logs   = [];

if( ! file_exists($import_yml))
{
  echo '<p style="color:#a00">import.yml missing: '.htmlspecialchars($import_yml).'</p>';
  exit;
}

try {
  $import_map = Yaml::parseFile($import_yml) ?: [];
}
catch( Throwable $e ) {
  echo '<p style="color:#a00">Error parsing import.yml</p>';
  exit;
}

if( ! is_array($import_map)) $import_map = [];

// Helpers
function read_lines( string $path ) : array
{
  $content = file_get_contents($path);
  // Normalize to \n for processing, keep original line endings insight (we will write with \n)
  return preg_split("/\r?\n/", $content);
}

function write_lines( string $path, array $lines ) : bool
{
  $data = implode("\n", $lines);
  return file_put_contents($path, $data) !== false;
}

function find_key_line( array $lines, string $key ) : int
{
  $pattern = '/^\s*'.preg_quote($key, '/').':\s*(.*)$/u';
  foreach( $lines as $i => $line)
  {
    if( preg_match($pattern, $line)) return $i;
  }
  return -1;
}

function read_scalar_value( array $lines, string $key ) : ?string
{
  $idx = find_key_line($lines, $key);
  if( $idx === -1) return null;
  if( preg_match('/^\s*'.preg_quote($key,'/').':\s*(.*)$/u', $lines[$idx], $m))
  {
    $val = trim($m[1]);
    return $val !== '' ? $val : null;
  }
  return null;
}

function set_scalar_value( array &$lines, string $key, string $value ) : void
{
  $idx = find_key_line($lines, $key);
  // Determine indentation to preserve
  $indent = '';
  if( $idx !== -1 && preg_match('/^(\s*)'.preg_quote($key,'/').':/u', $lines[$idx], $m))
    $indent = $m[1];
  $newLine = build_aligned_line($indent, $key, $value);
  if( $idx === -1)
  {
    // append near end but before trailing empty lines
    $insertAt = count($lines);
    while( $insertAt > 0 && trim($lines[$insertAt-1]) === '') $insertAt--;
    array_splice($lines, $insertAt, 0, [$newLine]);
  }
  else {
    // preserve trailing inline comment if present
    $comment = '';
    if( preg_match('/(\s+#.*)$/u', $lines[$idx], $mC)) $comment = $mC[1];
    $lines[$idx] = $newLine.$comment;
  }
}

// Insert or set a key, creating it directly after an anchor key if it doesn't yet exist
function set_scalar_value_after( array &$lines, string $key, string $value, string $afterKey ) : void
{
  $idx = find_key_line($lines, $key);
  $indent = '';
  if( $idx !== -1 && preg_match('/^(\s*)'.preg_quote($key,'/').':/u', $lines[$idx], $m))
    $indent = $m[1];
  $newLine = build_aligned_line($indent, $key, $value);
  if( $idx !== -1)
  {
    // preserve trailing inline comment if present
    $comment = '';
    if( preg_match('/(\s+#.*)$/u', $lines[$idx], $mC)) $comment = $mC[1];
    $lines[$idx] = $newLine.$comment;
    return;
  }

  // create below the anchor if present
  $anchorIdx = find_key_line($lines, $afterKey);
  if( $anchorIdx !== -1)
  {
    // preserve indentation of anchor line
    $indent = '';
    if( preg_match('/^(\s*)'.preg_quote($afterKey,'/').':/u', $lines[$anchorIdx], $m))
      $indent = $m[1];
    $newLine = build_aligned_line($indent, $key, $value);
    array_splice($lines, $anchorIdx + 1, 0, [$newLine]);
    return;
  }

  // fallback to generic setter (append near end)
  set_scalar_value($lines, $key, $value);
}

function find_section_start( array $lines, string $sectionKey ) : int
{
  $pattern = '/^\s*'.preg_quote($sectionKey,'/').':\s*$/u';
  foreach( $lines as $i => $line)
  {
    if( preg_match($pattern, $line)) return $i;
  }
  return -1;
}

function insert_history_entry( array &$lines, string $sectionKey, string $date, string $value, string $comment = '' ) : void
{
  $secIdx = find_section_start($lines, $sectionKey);
  $entry  = "  $date: $value";
  if( $comment !== '') $entry .= "  # $comment";

  if( $secIdx === -1)
  {
    // Append a new section at the end
    $tail = count($lines);
    // Ensure one empty line before new section for readability
    if( $tail === 0 || trim($lines[$tail-1]) !== '') $lines[] = '';
    $lines[] = $sectionKey.':';
    $lines[] = '';
    $lines[] = $entry;
  }
  else {
    // Insert right after the section header (to keep newest first)
    $insertAt = $secIdx + 1;
    // Skip an optional blank line just below header
    if( isset($lines[$insertAt]) && trim($lines[$insertAt]) === '') $insertAt++;
    array_splice($lines, $insertAt, 0, [$entry]);
  }
}

function normalize_number( $val ) : string
{
  if( is_numeric($val)) {
    $num = 0 + $val;
    // Always show two decimals (currency format)
    return number_format($num, 2, '.', '');
  }
  // Keep quoted string if needed
  return (string)$val;
}

// Build a key/value line with value starting at column 20 (1-based)
function build_aligned_line( string $indent, string $key, string $value, int $valueCol = 20 ) : string
{
  // Base part up to colon
  $base = $indent.$key.':';
  $len  = strlen($base);
  // Compute spaces so that value begins at column $valueCol
  $currentCol = $len + 1; // position of next character (1-based)
  $spaces = 1; // at least one space after colon
  if( $currentCol + $spaces < $valueCol)
    $spaces = $valueCol - $currentCol;
  return $base.str_repeat(' ', $spaces).$value;
}

function find_food_file( string $foods_dir, string $name ) : ?array
{
  // First try direct file/folder match (base foods)
  $fileA = "$foods_dir/$name.yml";
  if( is_file($fileA)) return ['mode' => 'file', 'path' => $fileA, 'isVariant' => false];

  $dir = "$foods_dir/$name";
  $fileB = "$dir/$name-this.yml";
  if( is_dir($dir) && is_file($fileB)) return ['mode' => 'this', 'path' => $fileB, 'isVariant' => false];

  // If not found, check if it's a variant using our helper function
  $sourceInfo = bulk_find_food_source( $name, $foods_dir );
  if( $sourceInfo && $sourceInfo['isVariant'])
  {
    $mode = strpos($sourceInfo['file'], '-this.yml') !== false ? 'this' : 'file';
    return [
      'mode' => $mode, 
      'path' => $sourceInfo['file'], 
      'isVariant' => true,
      'variantName' => $sourceInfo['variantName']
    ];
  }

  return null;
}

$updated = 0;
$skipped = 0;

foreach( $import_map as $food_name => $entry)
{
  if( ! is_array($entry)) { $skipped++; continue; }

  $dest = find_food_file($foods_dir, $food_name);
  if( ! $dest) {
    $errors[] = "Destination for '$food_name' not found";
    $skipped++;
    continue;
  }

  $new_price     = array_key_exists('price', $entry) ? normalize_number($entry['price']) : null;
  $new_deal      = array_key_exists('dealPrice', $entry) ? normalize_number($entry['dealPrice']) : null;

  $success = false;

  if( $dest['isVariant'])
  {
    // Handle variant foods using our simple helper functions
    $priceSuccess = true;
    $dealSuccess = true;
    
    if( $new_price !== null)
    {
      $priceSuccess = bulk_update_price( $food_name, $new_price, $foods_dir );
    }
    
    if( $new_deal !== null)
    {
      $dealSuccess = bulk_update_deal_price( $food_name, $new_deal, $foods_dir );
    }
    
    $success = $priceSuccess && $dealSuccess;
  }
  else
  {
    // Handle regular foods with full history support
    $path  = $dest['path'];
    $lines = read_lines($path);

    // Read current values from file
    $cur_price     = read_scalar_value($lines, 'price');
    $cur_deal      = read_scalar_value($lines, 'dealPrice');
    $cur_last_upd  = read_scalar_value($lines, 'lastPriceUpd');

    $import_last   = array_key_exists('lastPriceUpd', $entry) ? (string)$entry['lastPriceUpd'] : null;
    $today         = (new DateTime())->format('Y-m-d');

    // 1) Move old current price(s) to history using lastPriceUpd as date
    if( $cur_last_upd !== null && $cur_last_upd !== '')
    {
      if( $cur_price !== null && $cur_price !== '')
        insert_history_entry($lines, 'prices', $cur_last_upd, $cur_price);
      if( $cur_deal !== null && $cur_deal !== '')
        insert_history_entry($lines, 'dealPrices', $cur_last_upd, $cur_deal);
    }

    // 2) Set new prices if provided (create keys if missing)
    if( $new_price !== null)
      set_scalar_value($lines, 'price', $new_price);
    if( $new_deal !== null)
      set_scalar_value_after($lines, 'dealPrice', $new_deal, 'price');

    // 3) Update lastPriceUpd to today
    $new_last_upd = $import_last !== null && $import_last !== '' ? $import_last : $today;
    set_scalar_value($lines, 'lastPriceUpd', $new_last_upd);

    $success = write_lines($path, $lines);
  }

  if( $success ) {
    $updated++;
    $logs[] = "Updated: ".htmlspecialchars($food_name) . ($dest['isVariant'] ? ' (variant)' : '');
  }
  else {
    $errors[] = "Failed to update: ".htmlspecialchars($food_name);
  }
}

// Output summary
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bulk price import</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; padding: 16px; }
    .ok { color: #0a0; }
    .err { color: #a00; }
    ul { line-height: 1.6; }
  </style>
</head>
<body>
  <h3>Bulk price import</h3>
  <p>Source: <code><?= htmlspecialchars($import_yml) ?></code></p>
  <p>Destination: <code><?= htmlspecialchars($foods_dir) ?></code></p>

  <p class="ok">Updated: <?= $updated ?>, Skipped: <?= $skipped ?>, Errors: <?= count($errors) ?></p>

  <?php if( $logs): ?>
    <h4>Updates</h4>
    <ul>
      <?php foreach( $logs as $msg): ?>
        <li><?= $msg ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php if( $errors): ?>
    <h4 class="err">Errors</h4>
    <ul>
      <?php foreach( $errors as $msg): ?>
        <li class="err"><?= htmlspecialchars($msg) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</body>
</html>
