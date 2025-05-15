<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once '../src/vendor/autoload.php';

// Use hardcoded user ID - Default is typically used in the system
$user_id = 'JaneDoe@example.com-24080101000000';

// Default values
$days_old = isset($_GET['days']) ? intval($_GET['days']) : 180; // 6 months default
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'vendor';
$filter_vendor = isset($_GET['vendor']) ? $_GET['vendor'] : '';
$show_missing = isset($_GET['missing']) ? ($_GET['missing'] === '1') : true;
$show_old = isset($_GET['old']) ? ($_GET['old'] === '1') : true;

// Load food data from individual files
$foods_dir = '../src/data/bundles/Default_' . $user_id . '/foods';
$foods = [];
$error_message = '';

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
      padding: 20px;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
    }
    
    h1 {
      color: var(--dark-color);
      margin-bottom: 20px;
      text-align: center;
    }
    
    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
      padding: 15px;
      background-color: var(--light-color);
      border-radius: 6px;
    }
    
    .filter-group {
      flex: 1 1 200px;
    }
    
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: var(--dark-color);
    }
    
    select, input {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
    }
    
    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .checkbox-group input {
      width: auto;
    }
    
    button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.2s;
    }
    
    button:hover {
      background-color: var(--secondary-color);
    }
    
    .results {
      overflow-x: auto;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    th {
      background-color: var(--dark-color);
      color: white;
      position: sticky;
      top: 0;
    }
    
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    
    tr:hover {
      background-color: #f1f1f1;
    }
    
    .missing {
      background-color: rgba(231, 76, 60, 0.1);
    }
    
    .old {
      background-color: rgba(243, 156, 18, 0.1);
    }
    
    .status {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 3px;
      font-size: 12px;
      font-weight: bold;
      color: white;
    }
    
    .status.missing {
      background-color: var(--warning-color);
    }
    
    .status.old {
      background-color: var(--info-color);
    }
    
    .summary {
      margin-top: 20px;
      padding: 15px;
      background-color: var(--light-color);
      border-radius: 6px;
      text-align: center;
    }
    
    @media (max-width: 768px) {
      .filters {
        flex-direction: column;
      }
      
      th, td {
        padding: 8px 10px;
      }
      
      .container {
        padding: 10px;
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
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Vendor</th>
            <th>Price</th>
            <th>Deal Price</th>
            <th>Last Updated</th>
            <th>Days Since Update</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($results)): ?>
            <tr>
              <td colspan="7" style="text-align: center;">No items found matching the criteria</td>
            </tr>
          <?php endif; ?>
          
          <?php foreach ($results as $item): ?>
            <tr class="<?= $item['is_missing'] ? 'missing' : ($item['is_old'] ? 'old' : '') ?>">
              <td><?= htmlspecialchars($item['name']) ?></td>
              <td><?= htmlspecialchars($item['vendor']) ?></td>
              <td><?= htmlspecialchars($item['price']) ?></td>
              <td><?= htmlspecialchars($item['dealPrice']) ?></td>
              <td><?= htmlspecialchars($item['lastPriceUpd']) ?></td>
              <td><?= $item['days_since_update'] !== null ? $item['days_since_update'] : 'N/A' ?></td>
              <td>
                <?php if ($item['is_missing']): ?>
                  <span class="status missing">Missing</span>
                <?php elseif ($item['is_old']): ?>
                  <span class="status old">Outdated</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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
