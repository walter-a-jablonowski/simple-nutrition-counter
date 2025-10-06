<?php

chdir('../..');

// Made with AI only

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once __DIR__ . '/lib/variant_helper.php';

// TASK: Use hardcoded user ID - Default is typically used in the system
$user_id = 'JaneDoe@example.com-24080101000000';

// Prepare error message holder early so config parsing can report issues
$error_message = '';

// Load initial filters exclusively from local config.yml (if present)
$config_defaults = [];
$cfgFile = __DIR__ . '/config.yml';
try {
  if( file_exists($cfgFile))
  {
    $cfg = Yaml::parseFile($cfgFile);
    if( is_array($cfg) && isset($cfg['initialFilters']) && is_array($cfg['initialFilters']))
      $config_defaults = $cfg['initialFilters'];
  }
  else {
    $error_message = 'Config file missing: ' . $cfgFile;
  }
}
catch( ParseException $e ) {
  $error_message = 'Invalid config.yml: ' . $e->getMessage();
}

// Resolve effective filters: query string overrides config defaults
$days_old      = isset($_GET['days'])    ? intval($_GET['days']) : (isset($config_defaults['days']) ? intval($config_defaults['days']) : null);
$sort_by       = isset($_GET['sort'])    ? $_GET['sort'] : (string)($config_defaults['sort'] ?? '');
// Normalize deprecated sort option
if( $sort_by === 'date') $sort_by = 'days';
$filter_vendor = isset($_GET['vendor'])  ? $_GET['vendor'] : (string)($config_defaults['vendor'] ?? '');
$show_missing  = isset($_GET['missing']) ? ($_GET['missing'] === '1') : (bool)($config_defaults['missing'] ?? false);
$show_old      = isset($_GET['old'])     ? ($_GET['old'] === '1') : (bool)($config_defaults['old'] ?? false);

// Load food data from individual files
$foods_dir     = "data/bundles/Default_$user_id/foods";
$foods         = [];
$vendors       = ['all' => true];

if( ! is_dir($foods_dir)) {
  $error_message = 'Foods directory missing: ' . $foods_dir;
}
else {
  
  // Function to recursively scan directory for YAML files
  function scan_food_dir($dir, &$foods)
  {
    foreach( scandir($dir) as $item)
    {
      if( $item === '.' || $item === '..')  continue;
      
      if( is_dir("$dir/$item"))
      {
        // Check if this is a food folder with a -this.yml file
        if( file_exists("$dir/$item/$item-this.yml"))
        {
          try {
            $food_data = Yaml::parseFile("$dir/$item/$item-this.yml");
            $food_name = $item; // Use folder name as food name
            
            // Expand variants if they exist
            $expanded = bulk_expand_variants( $food_name, $food_data );
            foreach( $expanded as $name => $data )
              $foods[$name] = $data;

          } catch( ParseException $e ) {
            // Skip files that can't be parsed
            continue;
          }
        }
        else
        {
          // Regular subfolder, continue scanning
          scan_food_dir("$dir/$item", $foods);
        }
      }
      elseif( pathinfo("$dir/$item", PATHINFO_EXTENSION) === 'yml')
      {
        // Skip template files that start with underscore
        if( substr($item, 0, 1) === '_')
          continue;
          
        // Skip special -this.yml files
        if( strpos($item, '-this.yml') !== false)
          continue;
        
        try {
          $food_data = Yaml::parseFile("$dir/$item");
          $food_name = pathinfo($item, PATHINFO_FILENAME);
          
          // Expand variants if they exist
          $expanded = bulk_expand_variants( $food_name, $food_data );
          foreach( $expanded as $name => $data )
            $foods[$name] = $data;
        }
        catch( ParseException $e ) {
          // Skip files that can't be parsed
          continue;
        }
      }
    }
  }
  
  // Scan the foods directory
  scan_food_dir($foods_dir, $foods);
}

// Process food data
$results  = [];
$vendors  = ['all' => true];
$this_day = new DateTime();

// Debug info
$foods_count = count($foods);
$debug_info = "Found $foods_count food items in $foods_dir";

foreach( $foods as $food_name => $food)
{
  $vendor = isset($food['vendor']) ? $food['vendor'] : 'none';
  $vendors[$vendor] = true;
  
  $has_price = ! empty($food['price']) || ! empty($food['dealPrice']);
  
  // Handle lastPriceUpd which could be a date string in YYYY-MM-DD format
  $last_price_update = null;
  $days_since_update = null;
  
  if( isset($food['lastPriceUpd']))
  {
    // It's a timestamp
    if( is_numeric($food['lastPriceUpd']))
      $last_price_update = (new DateTime())->setTimestamp($food['lastPriceUpd']);
    // Try to parse as date string
    elseif( is_string($food['lastPriceUpd']) && ! empty($food['lastPriceUpd'])) {
      try {
        $last_price_update = new DateTime($food['lastPriceUpd']);
      }
      catch( Exception $e ) {
        // Invalid date format, ignore
      }
    }
    
    if( $last_price_update )
      $days_since_update = $this_day->diff($last_price_update)->days;
  }
  
  $is_old     = $days_since_update !== null && $days_since_update > $days_old;
  $is_missing = ! $has_price;
  
  // Filter based on criteria
  if( ( $show_missing && $is_missing) || ($show_old && $is_old))
    if( empty($filter_vendor) || $filter_vendor === 'all' || $vendor === $filter_vendor) {
      $results[] = [
        'name'              => $food_name,
        'vendor'            => $vendor,
        'price'             => $food['price'] ?? '',
        'dealPrice'         => $food['dealPrice'] ?? '',
        'lastPriceUpd'      => $last_price_update ? $last_price_update->format('Y-m-d') : '',
        'days_since_update' => $days_since_update,
        'is_missing'        => $is_missing,
        'is_old'            => $is_old,
        'productName'       => $food['productName'] ?? '',
        'weight'            => $food['weight'] ?? '',
        'pieces'            => $food['pieces'] ?? ''
      ];
    }
}

// Sort results
if( $sort_by === 'name') {
  usort($results, function($a, $b) {
    return strcmp($a['name'], $b['name']);
  });
}
elseif( $sort_by === 'days') {
  usort($results, function($a, $b) {
    // n/a (no date) first
    if( $a['days_since_update'] === null && $b['days_since_update'] === null) return 0;
    if( $a['days_since_update'] === null) return -1;
    if( $b['days_since_update'] === null) return 1;
    // then by days desc (oldest first)
    return $b['days_since_update'] - $a['days_since_update'];
  });
}
elseif( $sort_by === 'price') {
  usort($results, function($a, $b) {
    // Determine numeric price: prefer regular price, else dealPrice
    $ap = ($a['price'] !== '' ? floatval($a['price']) : ($a['dealPrice'] !== '' ? floatval($a['dealPrice']) : null));
    $bp = ($b['price'] !== '' ? floatval($b['price']) : ($b['dealPrice'] !== '' ? floatval($b['dealPrice']) : null));

    // n/a (no price data) first
    if( $ap === null && $bp === null) return 0;
    if( $ap === null) return -1;
    if( $bp === null) return 1;

    // then by price desc (higher first)
    if( $ap === $bp) return 0;
    return ($ap < $bp) ? 1 : -1;
  });
}
else { // vendor
  usort($results, function($a, $b) {
    $vendor_cmp = strcmp($a['vendor'], $b['vendor']);
    return $vendor_cmp !== 0 ? $vendor_cmp : strcmp($a['name'], $b['name']);
  });
}

// Load import.yml to know which items already have new prices
$import_map = [];
$import_file = __DIR__ . '/data/import.yml';
if( file_exists($import_file)) {
  try {
    $parsed_import = Yaml::parseFile($import_file);
    if( is_array($parsed_import)) $import_map = $parsed_import;
  }
  catch( \Exception $e ) {
    // ignore, keep empty map
  }
}

require 'view.php';

?>
