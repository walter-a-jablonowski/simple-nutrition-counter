<?php

require_once 'data.php';

$range = isset($_GET['range']) ? (string)$_GET['range'] : '6m';
$controller = new PricesReportController();
$report = $controller->getData($range);
$items = $report['items'];

?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Price Changes Report</title>
  <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>
  <div class="container">
    <h3>Price Changes</h3>

    <div class="controls">
      <label for="range">Range</label>
      <select id="range" name="range">
        <option value="1m" <?= $range==='1m'?'selected':'' ?>>1 Month</option>
        <option value="2m" <?= $range==='2m'?'selected':'' ?>>2 Months</option>
        <option value="3m" <?= $range==='3m'?'selected':'' ?>>3 Months</option>
        <option value="6m" <?= $range==='6m'?'selected':'' ?>>6 Months</option>
        <option value="1y" <?= $range==='1y'?'selected':'' ?>>1 year</option>
        <option value="all" <?= $range==='all'?'selected':'' ?>>All</option>
      </select>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th class="num">First price</th>
          <th class="num">Current price</th>
          <th class="num">Change %</th>
          <th class="num">First deal</th>
          <th class="num">Current deal</th>
          <th class="num">Change % (deal)</th>
          <th>Last update</th>
        </tr>
      </thead>
      <tbody>
        <?php if( empty($items)): ?>
          <tr><td colspan="8" class="empty">No items in this range</td></tr>
        <?php else: ?>
          <?php foreach( $items as $it ): ?>
            <?php 
              $pct  = isset($it['pct']) && $it['pct'] !== null ? number_format($it['pct'], 1, '.', '') : '';
              $pctD = isset($it['pctDeal']) && $it['pctDeal'] !== null ? number_format($it['pctDeal'], 1, '.', '') : '';
              $rowWarn = ($pct !== '' && (float)$pct >= 10.0) || ($pctD !== '' && (float)$pctD >= 10.0);
            ?>
            <tr class="<?= $rowWarn ? 'warn' : '' ?>">
              <td><?= htmlspecialchars($it['name']) ?></td>
              <td class="num"><?= $it['firstPrice'] !== null ? number_format($it['firstPrice'], 2, '.', '') : '' ?></td>
              <td class="num"><?= $it['price'] !== null ? number_format($it['price'], 2, '.', '') : '' ?></td>
              <td class="num strong"><?= $pct !== '' ? ($pct.'%') : '' ?></td>
              <td class="num"><?= $it['firstDeal'] !== null ? number_format($it['firstDeal'], 2, '.', '') : '' ?></td>
              <td class="num"><?= $it['dealPrice'] !== null ? number_format($it['dealPrice'], 2, '.', '') : '' ?></td>
              <td class="num strong"><?= $pctD !== '' ? ($pctD.'%') : '' ?></td>
              <td><?= htmlspecialchars($it['lastPriceUpd']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <script>
    document.getElementById('range').addEventListener('change', function(){
      const params = new URLSearchParams(window.location.search);
      params.set('range', this.value);
      window.location.search = params.toString();
    });
  </script>
</body>
</html>
