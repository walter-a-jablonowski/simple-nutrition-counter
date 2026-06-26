<!-- Charts layout (nutrition charts; hidden by default) -->

<div id="chartsLayout" class="row g-0 flex-grow-1 h-100 d-none">
  <div class="col-12 h-100">
    <div class="content-wrapper h-100 overflow-auto d-flex flex-column">

      <!-- Header (duplicate of main; wired live to the same handlers) -->

      <header class="bg-light border-bottom py-2 px-2 text-break fs-5 d-flex align-items-center">
        <div class="d-flex align-items-center">

          <?php require( __DIR__ . '/app_logo.php'); ?>

          <select onchange="mainCrl.userSelectChange(event)" class="js-userSelect border-0 fs-5">
            <?php foreach( User::getAll() as $userId ): ?>
              <option value="<?= $userId ?>"<?= self::iif( $userId == User::current('id'), ' selected') ?>>
                <?= User::byId( $userId )->get('name') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <span class="ms-auto fs-6 text-secondary">Charts</span>
      </header>

      <!-- Scrollable content area -->

      <div class="flex-grow-1 overflow-auto p-3">

        <div class="charts-view-controls d-flex flex-wrap align-items-center gap-2 mb-3">
          <select id="charts-date-range" class="form-select form-select-sm w-auto" title="Limit date range">
            <option value="1m">1 Month</option>
            <option value="2m">2 Months</option>
            <option value="3m">3 Month</option>
            <option value="6m" selected>6 Month</option>
            <option value="1y">1 year</option>
            <option value="all">All</option>
          </select>
          <button class="charts-view-btn btn btn-sm btn-outline-secondary active" data-view="all">Show All</button>
          <button class="charts-view-btn btn btn-sm btn-outline-secondary" data-view="data">Data</button>
          <button class="charts-view-btn btn btn-sm btn-outline-secondary" data-view="average">Moving Average</button>
          <button class="charts-view-btn btn btn-sm btn-outline-secondary" data-toggle="no-unprecise" title="Exclude days with unprecise header">No unprecise</button>
          <button class="charts-view-btn btn btn-sm btn-outline-secondary" data-toggle="no-unprecisetime" title="Exclude days with unpreciseTime header">No unpr. time</button>
        </div>

        <div id="charts-loading" class="text-secondary small mb-3">Loading charts&hellip;</div>

        <div class="charts-container">
          <div class="chart-wrapper">
            <h2>
              Eating Time (min)
              <span class="badge period-avg" id="eatingTime-period-avg"></span>
              <span class="badge avg-avg" id="eatingTime-avg-avg"></span>
            </h2>
            <div class="info-text">( i ) days with no data are excluded from averages</div>
            <canvas id="eatingTimeChart"></canvas>
          </div>
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
      </div>
    </div>
  </div>
</div>
