<?php

  use Symfony\Component\Yaml\Yaml;
  use Symfony\Component\Yaml\Exception\ParseException;

  $a = Yaml::parse( file_get_contents('data/bundles/Default_' . User::current('id') . '/-this.yml'));

?>

<!-- Nutrition summary widgets (replaces previous #quickSummary row, was: col-6 col-md-2 col-xxl-2 ...) -->

<section class="nutrition-widgets py-2 px-3 border-bottom">
  <h5 class="mb-2 visually-hidden">Nutrition Summary</h5>

  <div class="overflow-auto position-relative">

    <button class="scroll-arrow" aria-label="Scroll right">
      <i class="bi bi-arrow-right"></i>
    </button>

    <div id="quickSummary" class="widgets-container">

      <!-- Strategy (yellow widget, only if user defined a headline) -->

      <?php if( User::current()->has('myStrategy.headline')): ?>
        <div id="strategy" class="nutrition-widget widget-yellow border rounded text-center"
             data-bs-toggle = "modal"
             data-bs-target = "#infoModal"
             data-title     = "&#x3C;span class=&#x22;fs-4&#x22;&#x3E;<?= User::current()->get('myStrategy.headline') ?>&#x3C;/span&#x3E;"
             data-source    = "#myStrategyData"
        >
          <div class="widget-label fw-bold">Strategy</div>
          <div class="widget-value"><?= User::current()->get('myStrategy.headline') ?></div>
        </div>

        <?php if( User::current()->has('myStrategy.content')): ?>
          <div id="myStrategyData" class="d-none">
            <span class="fs-5"><?= User::current()->get('myStrategy.content') ?></span>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Calories (red) -->

      <div class="nutrition-widget widget-red border rounded text-center"
           data-bs-toggle = "modal"
           data-bs-target = "#tipsModal"
      >
        <div class="widget-label fw-bold">kcal <?= $a['primaryGoals']['calories']['label'] ?></div>
        <div class="widget-value">
          <span id="caloriesSum">0</span> in <span id="timeSum">00:00</span>
        </div>
      </div>

      <!-- Fat / amino -->

      <div class="nutrition-widget border rounded bg-white text-center"
           data-bs-toggle = "modal"
           data-bs-target = "#tipsModal"
      >
        <div class="widget-label fw-bold">Fat / Amino <?= $a['primaryGoals']['fat:amino']['label'] ?></div>
        <div class="widget-value">
          <span id="fatSum">0</span> / <span id="aminoSum">0</span> g
        </div>
      </div>

      <!-- (TASK) space saving quick summary design -->
<!--
      <div class="nutrition-widget border rounded bg-white text-center">
        <div class="widget-label fw-bold">Carbs</div>
        <div class="widget-value"><span id="carbsSum">0</span> g</div>
      </div>
-->

      <!-- Carbs (sugar) -->

      <div class="nutrition-widget border rounded bg-white text-center"
           data-bs-toggle = "modal"
           data-bs-target = "#tipsModal"
      >
        <div class="widget-label fw-bold">Carbs <?= $a['primaryGoals']['carbs']['label'] ?> (sugar)</div>
        <div class="widget-value">
          <span id="carbsSum">0</span> g (<span id="sugarSum">0</span>)
        </div>
      </div>

      <!-- Fibre -->

      <div class="nutrition-widget border rounded bg-white text-center"
           data-bs-toggle = "modal"
           data-bs-target = "#tipsModal"
      >
        <div class="widget-label fw-bold">Fibre <?= $a['primaryGoals']['fibre']['label'] ?></div>
        <div class="widget-value"><span id="fibreSum">0</span> g</div>
      </div>

      <!-- Salt -->

      <div class="nutrition-widget border rounded bg-white text-center"
           data-bs-toggle = "modal"
           data-bs-target = "#tipsModal"
      >
        <div class="widget-label fw-bold">Salt <?= $a['primaryGoals']['salt']['label'] ?></div>
        <div class="widget-value"><span id="saltSum">0</span> g</div>
      </div>

      <!-- Price (green) -->

      <div class="nutrition-widget widget-green border rounded text-center">
        <div class="widget-label fw-bold">Price</div>
        <div class="widget-value">
          <?= settings::get('currencySymbol') ?> <span id="priceSum">0</span>
        </div>
      </div>

    </div>
  </div>
</section>
