<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bulk price upd</title>
  <?php if( ! empty($error_message)): ?>
  <script>
    console.error("<?= htmlspecialchars($error_message) ?>");
  </script>
  <?php endif; ?>
  <link href="styles.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>
  <div class="container">
    <h3>Bulk price upd</h3>
    
    <form method="get" class="filters">
      <div class="filter-group">
        <input id="search-filter" type="text" placeholder="Search...">
      </div>
      
      <div class="filter-group">
        <select id="days" name="days" class="auto-submit">
          <option value="30" <?= $days_old === 30 ? 'selected' : '' ?>>30 days</option>
          <option value="60" <?= $days_old === 60 ? 'selected' : '' ?>>60 days</option>
          <option value="90" <?= $days_old === 90 ? 'selected' : '' ?>>90 days</option>
          <option value="180" <?= $days_old === 180 ? 'selected' : '' ?>>180 days</option>
          <option value="365" <?= $days_old === 365 ? 'selected' : '' ?>>365 days</option>
        </select>
      </div>
      
      <div class="filter-group">
        <select id="vendor" name="vendor" class="auto-submit">
          <option value="all" <?= $filter_vendor === 'all' || empty($filter_vendor) ? 'selected' : '' ?>>All vendors</option>
          <?php foreach( array_keys($vendors) as $vendor): ?>
            <?php if( $vendor !== 'all'): ?>
              <option value="<?= htmlspecialchars($vendor) ?>" <?= $filter_vendor === $vendor ? 'selected' : '' ?>>
                <?= htmlspecialchars($vendor) ?>
              </option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="filter-group">
        <select id="sort" name="sort" class="auto-submit">
          <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Name</option>
          <option value="days" <?= $sort_by === 'days' ? 'selected' : '' ?>>Days Since Update</option>
          <option value="price" <?= $sort_by === 'price' ? 'selected' : '' ?>>Price</option>
        </select>
      </div>
      
      <div class="filter-group">
        <div class="checkbox-group">
          <input type="checkbox" id="missing" name="missing" value="1" <?= $show_missing ? 'checked' : '' ?> class="auto-submit">
          <label for="missing">Show missing prices</label>
        </div>
        
        <div class="checkbox-group">
          <input type="checkbox" id="old" name="old" value="1" <?= $show_old ? 'checked' : '' ?> class="auto-submit">
          <label for="old">Show old prices</label>
        </div>
      </div>
    </form>
    
    <div class="results">
      <?php if( empty($results)): ?>
        <div style="text-align: center; padding: 15px;">No items found matching the criteria</div>
      <?php else: ?>
        <!-- Desktop header -->
        <div class="list-header">
          <div class="list-col col-name">Name</div>
          <div class="list-col col-price">Price</div>
          <div class="list-col col-days">Days</div>
          <div class="list-col col-status">Status</div>
        </div>
        
        <!-- List items -->
        <?php foreach( $results as $item): ?>
          <?php $has_import = isset($import_map[$item['name']]); ?>
          <div class="list-row <?= $item['is_missing'] ? 'missing' : ($item['is_old'] ? 'old' : '') ?> <?= $has_import ? 'has-import' : '' ?>" data-name="<?= htmlspecialchars($item['name']) ?>">
            <!-- Desktop layout - grid columns -->
            <div class="list-col col-name"><?= htmlspecialchars($item['name']) ?></div>
            
            <div class="list-col col-price">
              <?php if( ! empty($item['price'])): ?>
                <span class="price-regular"><?= htmlspecialchars($item['price']) ?></span>
              <?php endif; ?>
              <?php if( ! empty($item['dealPrice'])): ?>
                <span class="price-deal"><?= htmlspecialchars($item['dealPrice']) ?></span>
              <?php endif; ?>
              <?php if( empty($item['price']) && empty($item['dealPrice'])): ?>
                <span>n/a</span>
              <?php endif; ?>
            </div>
            
            <div class="list-col col-days">
              <?= $item['days_since_update'] !== null ? $item['days_since_update'] : 'n/a' ?>
            </div>
            
            <!-- Status column (desktop only) -->
            <div class="list-col col-status">
              <?php if( $item['is_missing']): ?>
                <span class="status missing">Missing</span>
              <?php elseif( $item['is_old']): ?>
                <span class="status old">Outdated</span>
              <?php endif; ?>
            </div>
            
            <!-- Mobile view (only visible on small screens) -->
            <!-- First row: name and price -->
            <div class="mobile-main-row">
              <div class="mobile-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="mobile-price">
                <?php if( ! empty($item['price'])): ?>
                  <span class="price-regular"><?= htmlspecialchars($item['price']) ?></span>
                <?php endif; ?>
                <?php if( ! empty($item['dealPrice'])): ?>
                  <span class="price-deal"><?= htmlspecialchars($item['dealPrice']) ?></span>
                <?php endif; ?>
                <?php if( empty($item['price']) && empty($item['dealPrice'])): ?>
                  <span>n/a</span>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Second row: status and days -->
            <div class="list-row-details">
              <div>
                <?php if( $item['is_missing']): ?>
                  <span class="mobile-status missing">Missing price</span>
                <?php elseif( $item['is_old']): ?>
                  <span class="mobile-status old">Outdated</span>
                <?php endif; ?>
              </div>
              <div class="col-days"><?= $item['days_since_update'] !== null ? $item['days_since_update'].' days' : 'n/a' ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <div class="summary">
      <p>Found <?= count($results) ?> items that need attention (out of <?= $foods_count ?> items)</p>
      <?php if( ! empty($debug_info)): ?>
      <div class="debug-info">
        <p><small><?= htmlspecialchars($debug_info) ?></small></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Modal overlay for entering prices -->
  <div id="price-modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; width:min(420px,92vw); border-radius:8px; padding:16px; box-shadow:0 10px 30px rgba(0,0,0,.25);">
      <h4 id="price-modal-title" style="margin-bottom:12px;">Enter prices</h4>
      <div style="margin-bottom:10px;">
        <label for="price-input" style="display:block; font-weight:600; margin-bottom:4px;">Price</label>
        <input id="price-input" type="number" step="0.01" placeholder="e.g. 1.49" style="width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:4px;">
      </div>
      <div style="margin-bottom:10px;">
        <label for="dealprice-input" style="display:block; font-weight:600; margin-bottom:4px;">Deal price</label>
        <input id="dealprice-input" type="number" step="0.01" placeholder="e.g. 0.99" style="width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:4px;">
      </div>
      <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:14px;">
        <button id="price-cancel" type="button" style="background:#fff; color:#333; border:1px solid #ddd; padding:8px 12px; border-radius:4px; cursor:pointer;">Cancel</button>
        <button id="price-save" type="button" style="background:#3498db; color:#fff; border:0; padding:8px 12px; border-radius:4px; cursor:pointer;">Acccept</button>
      </div>
    </div>
  </div>
  
  <script>

    // Import map embedded from PHP
    const importMap = <?= json_encode($import_map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?> || {};

  </script>
  <script src="controller.js?v=<?= time() ?>"></script>
</body>
</html>
