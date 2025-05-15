<?php

// Made with AI only

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once '../src/vendor/autoload.php';

// TASK: Use hardcoded user ID - Default is typically used in the system
$user_id = 'JaneDoe@example.com-24080101000000';

// Default values
$days_old = isset($_GET['days']) ? intval($_GET['days']) : 180; // 6 months default
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$filter_vendor = isset($_GET['vendor']) ? $_GET['vendor'] : '';
$show_missing = isset($_GET['missing']) ? ($_GET['missing'] === '1') : true;
$show_old = isset($_GET['old']) ? ($_GET['old'] === '1') : true;

// Load food data from individual files
$foods_dir = '../src/data/bundles/Default_' . $user_id . '/foods';
$foods = [];
$error_message = '';
$vendors = ['all' => true];

if (!is_dir($foods_dir)) {
  $error_message = 'Foods directory not found: ' . $foods_dir;
} else {
  // Function to recursively scan directory for YAML files
  function scanFoodDir($dir, &$foods) {
    $items = scandir($dir);
    foreach ($items as $item) {
      if ($item === '.' || $item === '..') continue;
      
      $path = $dir . '/' . $item;
      if (is_dir($path)) {
        scanFoodDir($path, $foods);
      } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'yml') {
        // Skip template files that start with underscore
        if (substr($item, 0, 1) === '_') {
          continue;
        }
        
        try {
          $food_data = Yaml::parseFile($path);
          $food_name = pathinfo($item, PATHINFO_FILENAME);
          $foods[$food_name] = $food_data;
        } catch (ParseException $e) {
          // Skip files that can't be parsed
          continue;
        }
      }
    }
  }
  
  // Scan the foods directory
  scanFoodDir($foods_dir, $foods);
}

// Process food data
$results = [];
$vendors = ['all' => true];
$today = new DateTime();

// Debug info
$total_foods = count($foods);
$debug_info = "Found $total_foods food items in $foods_dir";

foreach ($foods as $food_name => $food) {
  $vendor = isset($food['vendor']) ? $food['vendor'] : 'none';
  $vendors[$vendor] = true;
  
  $has_price = !empty($food['price']) || !empty($food['dealPrice']);
  
  // Handle lastPriceUpd which could be a date string in YYYY-MM-DD format
  $last_price_update = null;
  $days_since_update = null;
  
  if (isset($food['lastPriceUpd'])) {
    if (is_numeric($food['lastPriceUpd'])) {
      // It's a timestamp
      $last_price_update = (new DateTime())->setTimestamp($food['lastPriceUpd']);
    } elseif (is_string($food['lastPriceUpd']) && !empty($food['lastPriceUpd'])) {
      // Try to parse as date string
      try {
        $last_price_update = new DateTime($food['lastPriceUpd']);
      } catch (Exception $e) {
        // Invalid date format, ignore
      }
    }
    
    if ($last_price_update) {
      $days_since_update = $today->diff($last_price_update)->days;
    }
  }
  
  $is_old = $days_since_update !== null && $days_since_update > $days_old;
  $is_missing = !$has_price;
  
  // Filter based on criteria
  if (($show_missing && $is_missing) || ($show_old && $is_old)) {
    if (empty($filter_vendor) || $filter_vendor === 'all' || $vendor === $filter_vendor) {
      $results[] = [
        'name' => $food_name,
        'vendor' => $vendor,
        'price' => $food['price'] ?? '',
        'dealPrice' => $food['dealPrice'] ?? '',
        'lastPriceUpd' => $last_price_update ? $last_price_update->format('Y-m-d') : '',
        'days_since_update' => $days_since_update,
        'is_missing' => $is_missing,
        'is_old' => $is_old
      ];
    }
  }
}

// Sort results
if ($sort_by === 'name') {
  usort($results, function($a, $b) {
    return strcmp($a['name'], $b['name']);
  });
} elseif ($sort_by === 'date') {
  usort($results, function($a, $b) {
    if ($a['lastPriceUpd'] === '' && $b['lastPriceUpd'] === '') return 0;
    if ($a['lastPriceUpd'] === '') return 1;
    if ($b['lastPriceUpd'] === '') return -1;
    return strcmp($b['lastPriceUpd'], $a['lastPriceUpd']);
  });
} elseif ($sort_by === 'days') {
  usort($results, function($a, $b) {
    if ($a['days_since_update'] === null && $b['days_since_update'] === null) return 0;
    if ($a['days_since_update'] === null) return 1;
    if ($b['days_since_update'] === null) return -1;
    return $b['days_since_update'] - $a['days_since_update'];
  });
} else { // vendor
  usort($results, function($a, $b) {
    $vendor_cmp = strcmp($a['vendor'], $b['vendor']);
    return $vendor_cmp !== 0 ? $vendor_cmp : strcmp($a['name'], $b['name']);
  });
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Price Checker</title>
  <?php if (!empty($error_message)): ?>
  <script>
    console.error("<?= htmlspecialchars($error_message) ?>");
  </script>
  <?php endif; ?>
  <style>
    :root {
      --primary-color: #3498db;
      --secondary-color: #2980b9;
      --warning-color: #e74c3c;
      --info-color: #f39c12;
      --light-color: #ecf0f1;
      --dark-color: #2c3e50;
      --border-color: #ddd;
      --spacing: 8px;
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      line-height: 1.6;
      color: #333;
      background-color: #f5f5f5;
      padding: var(--spacing);
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      padding: var(--spacing);
    }
    
    h1 {
      color: var(--dark-color);
      margin-bottom: var(--spacing);
      text-align: center;
      font-size: 24px;
    }
    
    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: var(--spacing);
      margin-bottom: var(--spacing);
      padding: var(--spacing);
      background-color: var(--light-color);
      border-radius: 6px;
    }
    
    .filter-group {
      flex: 1 1 150px;
    }
    
    label {
      display: block;
      margin-bottom: 4px;
      font-weight: bold;
      color: var(--dark-color);
      font-size: 14px;
    }
    
    select, input {
      width: 100%;
      padding: 6px 8px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-size: 14px;
    }
    
    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    .checkbox-group input {
      width: auto;
    }
    
    button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.2s;
      width: 100%;
    }
    
    button:hover {
      background-color: var(--secondary-color);
    }
    
    /* Table-like list using divs */
    .list-header {
      display: grid;
      grid-template-columns: minmax(0, 3fr) minmax(0, 1fr) minmax(0, 1fr);
      background-color: var(--dark-color);
      color: white;
      font-weight: bold;
      padding: 10px;
      border-radius: 4px 4px 0 0;
      margin-top: var(--spacing);
    }
    
    .list-row {
      display: grid;
      grid-template-columns: minmax(0, 3fr) minmax(0, 1fr) minmax(0, 1fr);
      border-bottom: 1px solid var(--border-color);
      padding: 8px 10px;
      position: relative;
      align-items: center;
    }
    
    .list-row:nth-child(even) {
      background-color: #f9f9f9;
    }
    
    .list-row:hover {
      background-color: #f1f1f1;
    }
    
    .list-row.missing {
      border-left: 3px solid var(--warning-color);
    }
    
    .list-row.old {
      border-left: 3px solid var(--info-color);
    }
    
    .list-col {
      padding-right: 10px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .col-name {
      font-weight: bold;
      padding-left: 0;
    }
    
    .col-price {
      text-align: right;
      white-space: nowrap;
    }
    
    .price-regular {
      color: #333;
    }
    
    .price-deal {
      color: var(--warning-color);
      margin-left: 5px;
    }
    
    .col-days {
      flex: 1;
      text-align: right;
    }
    
    /* Status badges */
    .status {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 12px;
      font-weight: bold;
      color: white;
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
    }
    
    .status.missing {
      background-color: var(--warning-color);
    }
    
    .status.old {
      background-color: var(--info-color);
    }
    
    .summary {
      margin-top: var(--spacing);
      padding: var(--spacing);
      background-color: var(--light-color);
      border-radius: 6px;
      text-align: center;
      font-size: 14px;
    }
    
    .debug-info {
      font-size: 12px;
      color: #666;
    }
    
    /* Common styles for both desktop and mobile */
    .price-deal {
      margin-left: 3px;
    }
    
    /* Desktop-only elements */
    @media (min-width: 769px) {
      .mobile-main-row,
      .list-row-details {
        display: none;
      }
    }
    
    /* Mobile styles */
    @media (max-width: 768px) {
      .filters {
        gap: 6px;
      }
      
      .filter-group {
        flex: 1 1 100%;
      }
      
      .list-header {
        display: none; /* Hide header on mobile */
      }
      
      .list-row {
        display: flex;
        flex-wrap: wrap;
        padding: 8px 10px;
        margin-bottom: 6px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      }
      
      /* Hide desktop grid columns on mobile */
      .list-row > .list-col {
        display: none;
      }
      
      /* Status badges */
      .status {
        display: none; /* Hide badges on mobile */
      }
      
      .mobile-status {
        font-size: 12px;
        font-weight: bold;
      }
      
      .mobile-status.missing {
        color: var(--warning-color);
      }
      
      .mobile-status.old {
        color: var(--info-color);
      }
      
      /* Mobile layout elements */
      .mobile-main-row,
      .list-row-details {
        display: flex;
        justify-content: space-between;
        width: 100%;
      }
      
      .list-row-details {
        font-size: 12px;
        color: #666;
        margin-top: 4px;
      }
      
      .mobile-name {
        font-weight: bold;
        padding: 0;
        margin-right: 10px;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
      }
      
      .mobile-price {
        text-align: right;
        white-space: nowrap;
        padding: 0;
      }
      
      .col-days {
        padding: 0;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Price Checker</h1>
    
    <form method="get" class="filters">
      <div class="filter-group">
        <label for="days">Days threshold:</label>
        <input type="number" id="days" name="days" value="<?= $days_old ?>" min="1" max="1000">
      </div>
      
      <div class="filter-group">
        <label for="vendor">Vendor:</label>
        <select id="vendor" name="vendor">
          <option value="all" <?= $filter_vendor === 'all' || empty($filter_vendor) ? 'selected' : '' ?>>All vendors</option>
          <?php foreach (array_keys($vendors) as $vendor): ?>
            <?php if ($vendor !== 'all'): ?>
              <option value="<?= htmlspecialchars($vendor) ?>" <?= $filter_vendor === $vendor ? 'selected' : '' ?>>
                <?= htmlspecialchars($vendor) ?>
              </option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="filter-group">
        <label for="sort">Sort by:</label>
        <select id="sort" name="sort">
          <option value="vendor" <?= $sort_by === 'vendor' ? 'selected' : '' ?>>Vendor</option>
          <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Name</option>
          <option value="date" <?= $sort_by === 'date' ? 'selected' : '' ?>>Last Update Date</option>
          <option value="days" <?= $sort_by === 'days' ? 'selected' : '' ?>>Days Since Update</option>
        </select>
      </div>
      
      <div class="filter-group">
        <div class="checkbox-group">
          <input type="checkbox" id="missing" name="missing" value="1" <?= $show_missing ? 'checked' : '' ?>>
          <label for="missing">Show missing prices</label>
        </div>
        
        <div class="checkbox-group">
          <input type="checkbox" id="old" name="old" value="1" <?= $show_old ? 'checked' : '' ?>>
          <label for="old">Show old prices</label>
        </div>
      </div>
      
      <div class="filter-group">
        <button type="submit">Apply Filters</button>
      </div>
    </form>
    
    <div class="results">
      <?php if (empty($results)): ?>
        <div style="text-align: center; padding: 15px;">No items found matching the criteria</div>
      <?php else: ?>
        <!-- Desktop header -->
        <div class="list-header">
          <div class="list-col col-name">Name</div>
          <div class="list-col col-price">Price</div>
          <div class="list-col col-days">Days</div>
        </div>
        
        <!-- List items -->
        <?php foreach ($results as $item): ?>
          <div class="list-row <?= $item['is_missing'] ? 'missing' : ($item['is_old'] ? 'old' : '') ?>">
            <!-- Desktop layout - grid columns -->
            <div class="list-col col-name"><?= htmlspecialchars($item['name']) ?></div>
            
            <div class="list-col col-price">
              <?php if (!empty($item['price'])): ?>
                <span class="price-regular"><?= htmlspecialchars($item['price']) ?></span>
              <?php endif; ?>
              <?php if (!empty($item['dealPrice'])): ?>
                <span class="price-deal"><?= htmlspecialchars($item['dealPrice']) ?></span>
              <?php endif; ?>
              <?php if (empty($item['price']) && empty($item['dealPrice'])): ?>
                <span>N/A</span>
              <?php endif; ?>
            </div>
            
            <div class="list-col col-days">
              <?= $item['days_since_update'] !== null ? $item['days_since_update'] : 'N/A' ?>
            </div>
            
            <!-- Status badges (desktop only) -->
            <?php if ($item['is_missing']): ?>
              <span class="status missing">Missing</span>
            <?php elseif ($item['is_old']): ?>
              <span class="status old">Outdated</span>
            <?php endif; ?>
            
            <!-- Mobile view (only visible on small screens) -->
            <!-- First row: name and price -->
            <div class="mobile-main-row">
              <div class="mobile-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="mobile-price">
                <?php if (!empty($item['price'])): ?>
                  <span class="price-regular"><?= htmlspecialchars($item['price']) ?></span>
                <?php endif; ?>
                <?php if (!empty($item['dealPrice'])): ?>
                  <span class="price-deal"><?= htmlspecialchars($item['dealPrice']) ?></span>
                <?php endif; ?>
                <?php if (empty($item['price']) && empty($item['dealPrice'])): ?>
                  <span>N/A</span>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Second row: status and days -->
            <div class="list-row-details">
              <div>
                <?php if ($item['is_missing']): ?>
                  <span class="mobile-status missing">Missing price</span>
                <?php elseif ($item['is_old']): ?>
                  <span class="mobile-status old">Outdated</span>
                <?php endif; ?>
              </div>
              <div class="col-days"><?= $item['days_since_update'] !== null ? $item['days_since_update'].' days' : 'N/A' ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <div class="summary">
      <p>Found <?= count($results) ?> items that need attention (out of <?= $total_foods ?> total items)</p>
      <?php if (!empty($debug_info)): ?>
      <div class="debug-info">
        <p><small><?= htmlspecialchars($debug_info) ?></small></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Add event listeners if needed
      
      // Example: Auto-submit form when certain filters change
      const autoSubmitElements = document.querySelectorAll('#vendor, #sort, #missing, #old');
      autoSubmitElements.forEach(element => {
        element.addEventListener('change', function() {
          document.querySelector('form').submit();
        });
      });
    });
  </script>
</body>
</html>
