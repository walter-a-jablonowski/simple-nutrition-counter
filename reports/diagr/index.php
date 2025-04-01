<?php

require_once '../../src/vendor/autoload.php'; 
require_once 'data.php';

$controller = new DiagramController();
$chartData  = $controller->getData();

?><!DOCTYPE html>
<html>
<head>
  <title>Nutrition Charts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="view-controls">
    <button class="view-btn active" data-view="all">Show All</button>
    <button class="view-btn" data-view="data">Data</button>
    <button class="view-btn" data-view="average">Moving Average</button>
  </div>

  <div class="charts-container">
    <div class="chart-wrapper">
      <h2>
        Calories (kcal)
        <span class="badge period-avg" id="calories-period-avg"></span>
        <span class="badge avg-avg" id="calories-avg-avg"></span>
      </h2>
      <canvas id="caloriesChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>
        Fat (g)
        <span class="badge period-avg" id="fat-period-avg"></span>
        <span class="badge avg-avg" id="fat-avg-avg"></span>
      </h2>
      <canvas id="fatChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>
        Carbs (g)
        <span class="badge period-avg" id="carbs-period-avg"></span>
        <span class="badge avg-avg" id="carbs-avg-avg"></span>
      </h2>
      <canvas id="carbsChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>
        Amino (g)
        <span class="badge period-avg" id="amino-period-avg"></span>
        <span class="badge avg-avg" id="amino-avg-avg"></span>
      </h2>
      <canvas id="aminoChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>
        Salt (g)
        <span class="badge period-avg" id="salt-period-avg"></span>
        <span class="badge avg-avg" id="salt-avg-avg"></span>
      </h2>
      <canvas id="saltChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>
        Price
        <span class="badge period-avg" id="price-period-avg"></span>
        <span class="badge avg-avg" id="price-avg-avg"></span>
      </h2>
      <canvas id="priceChart"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const chartData = <?php echo json_encode($chartData); ?>;
  </script>
  <script src="controller.js"></script>
</body>
</html> 