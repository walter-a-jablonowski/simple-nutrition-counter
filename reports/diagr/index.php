<?php

require_once '../../src/vendor/autoload.php'; 
require_once 'controller.php';

$controller = new DiagramController();
$chartData = $controller->getData();

?><!DOCTYPE html>
<html>
<head>
  <title>Nutrition Charts</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="charts-container">
    <div class="chart-wrapper">
      <h2>Calories (kcal)</h2>
      <canvas id="caloriesChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>Fat (g)</h2>
      <canvas id="fatChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>Carbs (g)</h2>
      <canvas id="carbsChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>Amino (g)</h2>
      <canvas id="aminoChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>Salt (g)</h2>
      <canvas id="saltChart"></canvas>
    </div>
    <div class="chart-wrapper">
      <h2>Price</h2>
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