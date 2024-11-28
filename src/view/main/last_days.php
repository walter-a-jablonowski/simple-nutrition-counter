<!-- TASK: maybe prefer

- keep nutrients group above (or add in below)
- nutrients like tab 2 but avg for a week here
- use a inner tab for the last days as well

time

- this week only
- this and last week (be able track a week avg even if week over)
- use floating like 7, 15 39 days as well?

-->
<div id="lastDaysView" class="scrollable-list border-0 mt-3">
  <ul class="head-view list-group mt-2">
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div class="time-switch dropdown">
        <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
          <span class="label fw-bold small">Last week</span>
        </button>
        <ul class="dropdown-menu" onclick="mainCrl.timeSwitchClick(event)">
          <li><a class="dropdown-item small" data-value="week"   href="#">Last week</a></li>
          <li><a class="dropdown-item small" data-value="days15" href="#">15 days</a></li>    <!-- selectors can't start with num -->
          <li><a class="dropdown-item small" data-value="days30" href="#">30 days</a></li>
        </ul>
      </div>
      <div>
        <span class="avg week"><?= $this->avg['price']['week'] ?> <?= settings::get('currencySymbol') ?></span>
        <span class="avg days15" style="display: none;"><?= $this->avg['price']['15days'] ?> <?= settings::get('currencySymbol') ?></span>
        <span class="avg days30" style="display: none;"><?= $this->avg['price']['30days'] ?> <?= settings::get('currencySymbol') ?></span>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Calories</div>
      <div>
        <span class="avg week"><?= $this->avg['calories']['week'] ?> kcal</span>
        <span class="avg days15" style="display: none;"><?= $this->avg['calories']['15days'] ?> kcal</span>
        <span class="avg days30" style="display: none;"><?= $this->avg['calories']['30days'] ?> kcal</span>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Fat / amino</div>
      <div>
        <span class="avg week"><?= $this->avg['fat']['week'] ?></span>
        <span class="avg days15" style="display: none;"><?= $this->avg['fat']['15days'] ?></span>
        <span class="avg days30" style="display: none;"><?= $this->avg['fat']['30days'] ?></span>
        /
        <span class="avg week"><?= $this->avg['amino']['week'] ?> g</span>
        <span class="avg days15" style="display: none;"><?= $this->avg['amino']['15days'] ?> g</span>
        <span class="avg days30" style="display: none;"><?= $this->avg['amino']['30days'] ?> g</span>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Carbs</div>
      <div>
        <span class="avg week"><?= $this->avg['carbs']['week'] ?> g</span>
        <span class="avg days15" style="display: none;"><?= $this->avg['carbs']['15days'] ?> g</span>
        <span class="avg days30" style="display: none;"><?= $this->avg['carbs']['30days'] ?> g</span>
      </div>
    </li>
    <!-- TASK: fibre -->
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Fibre</div>
      <div>
        <span class="avg week">(missing)</span>
        <span class="avg days15" style="display: none;">(missing)</span>
        <span class="avg days30" style="display: none;">(missing)</span>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Salt</div>
      <div>
        <span class="avg week"><?= $this->avg['salt']['week'] ?> g</span>
        <span class="avg days15" style="display: none;"><?= $this->avg['salt']['15days'] ?> g</span>
        <span class="avg days30" style="display: none;"><?= $this->avg['salt']['30days'] ?> g</span>
      </div>
    </li>
  </ul>
  <div class="days-view list-group mt-2">

<!--
    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        This week
      </button>
      <ul class="dropdown-menu">
        < ?php
          $start = date('Y-m-d', strtotime('last monday'));
          $end   = date('Y-m-d', strtotime('today'));
        ?>
        <li><a class="dropdown-item" href="#" data-from="< ?= $start ?>" data-to="< ?= $end ?>">This week</a></li>
        < ?php
          $start = date('Y-m-d', strtotime('monday -2 weeks'));
          $end   = date('Y-m-d', strtotime('today'));
        ?>
        <li><a class="dropdown-item" href="#" data-from="< ?= $start ?>" data-to="< ?= $end ?>">Last week</a></li>
        < ?php
          $start = date('Y-m-d', strtotime('monday -3 weeks'));
          $end   = date('Y-m-d', strtotime('today'));
        ?>
        <li><a class="dropdown-item" href="#" data-from="< ?= $start ?>" data-to="< ?= $end ?>">Last 2 weeks</a></li>
      </ul>
    </div>
-->
    <!-- ios like design -->

    <?php foreach( $this->lastDaysView->all() as $day => $sums ): ?>

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >
        <?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
        <small class="text-body-secondary mb-1">
          <b><?= $weekdays[ date('D', strtotime($day))] ?></b>&nbsp;&nbsp;<?= $day ?>
        </small>
        <small class="text-body-secondary mb-1">
          <b><?= $sums['price'] ?> <?= settings::get('currencySymbol') ?></b>
        </small>
      </li>

      <li class="list-group-item">
        <div class="row">
          <div class="col-6">
            <span><?= $sums['calories'] ?> kcal</span>
          </div>
          <div class="col-6">
            <span><?= $sums['fat'] ?> / <?= $sums['amino'] ?> g</span>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <span><?= $sums['carbs'] ?> g</span>
          </div>
          <div class="col-6">
            <span><?= $sums['salt'] ?> g</span>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </div>
</div>
