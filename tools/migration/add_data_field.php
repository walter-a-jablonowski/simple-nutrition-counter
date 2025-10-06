<?php

chdir('../../src');

// Add lastDealPriceUpd field after lastPriceUpd in all food files
// One-time migration tool - preserves YAML formatting

$foods_dir = 'data/bundles/Default_JaneDoe@example.com-24080101000000/foods';
$processed = 0;
$updated   = 0;
$skipped   = 0;

function process_file( $file_path )
{
  global $updated, $skipped;
  
  $content = file_get_contents($file_path);
  
  // Check if lastDealPriceUpd already exists
  if( preg_match('/^lastDealPriceUpd:/m', $content))
  {
    echo "  SKIP (already has lastDealPriceUpd): $file_path\n";
    $skipped++;
    return;
  }
  
  // Check if dealPrice exists at first level (not indented)
  if( ! preg_match('/^dealPrice:/m', $content))
  {
    echo "  SKIP (no dealPrice field): $file_path\n";
    $skipped++;
    return;
  }
  
  // Check if lastPriceUpd exists
  if( ! preg_match('/^(lastPriceUpd:\s*.*)$/m', $content, $matches))
  {
    echo "  SKIP (no lastPriceUpd found): $file_path\n";
    $skipped++;
    return;
  }
  
  // Get the lastPriceUpd line
  $lastPriceUpd_line = $matches[1];
  
  // Extract the value (everything after the colon and whitespace)
  preg_match('/^lastPriceUpd:\s*(.*)$/', $lastPriceUpd_line, $value_match);
  $value = trim($value_match[1]);
  
  // Create the new lastDealPriceUpd line with exactly 2 spaces (or just empty if no value)
  if( $value !== '')
    $new_line = "lastDealPriceUpd:  $value";
  else
    $new_line = "lastDealPriceUpd:";
  
  // Insert the new line after lastPriceUpd
  $new_content = preg_replace(
    '/^(lastPriceUpd:.*?)$/m',
    "$1\n$new_line",
    $content
  );
  
  // Write back to file
  file_put_contents($file_path, $new_content);
  
  echo "  UPDATE: $file_path\n";
  $updated++;
}

function scan_directory( $dir )
{
  global $processed;
  
  foreach( scandir($dir) as $item)
  {
    if( $item === '.' || $item === '..')
      continue;
    
    $path = "$dir/$item";
    
    if( is_dir($path))
    {
      // Check if this is a food folder with -this.yml
      $this_file = "$path/-this.yml";
      if( file_exists($this_file))
      {
        $processed++;
        process_file($this_file);
      }
      else
      {
        // Regular subfolder, continue scanning
        scan_directory($path);
      }
    }
    elseif( pathinfo($item, PATHINFO_EXTENSION) === 'yml')
    {
      // Skip template files
      if( substr($item, 0, 1) === '_')
        continue;
      
      $processed++;
      process_file($path);
    }
  }
}

echo "Starting migration: Adding lastDealPriceUpd field...\n\n";
scan_directory($foods_dir);

echo "\n";
echo "========================================\n";
echo "Migration complete!\n";
echo "Files processed: $processed\n";
echo "Files updated:   $updated\n";
echo "Files skipped:   $skipped\n";
echo "========================================\n";

?>
