<div id="lastDaysView" class="scrollable-list border-0 mt-3">
<!--
  <ul class="list-group mt-2">
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Week avg</div>
      <div>
        < ?= $this->priceAvg['week']  ?>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>15 days</div>
      <div>
        < ?= $this->priceAvg['15days'] ?>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>30 days</div>
      <div>
        < ?= $this->priceAvg['30days'] ?>
      </div>
    </li>
  </ul>
-->
<!-- TASK: MOV maybe we want avg data for nutrients as well -->
<!-- dorpdown version -->
  <ul class="list-group mt-2">
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div class="dropdown">
        <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
          <span class="fw-bold small">Last week</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item small" data="" href="#">Last week</a></li>
          <li><a class="dropdown-item small" data="" href="#">15 days</a></li>
          <li><a class="dropdown-item small" data="" href="#">30 days</a></li>
        </ul>
      </div>
      <div>
        <span class="avg week"><?= $this->avg['price']['week'] ?></span>
        <span class="avg 15days" style="display: none;"><?= $this->avg['price']['15days'] ?></span>
        <span class="avg 30days" style="display: none;"><?= $this->avg['price']['30days'] ?></span>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Calories</div>
      <div>
        <span class="avg week"><?= $this->avg['calories']['week'] ?></span>
        <span class="avg 15days" style="display: none;"><?= $this->avg['calories']['15days'] ?></span>
        <span class="avg 30days" style="display: none;"><?= $this->avg['calories']['30days'] ?></span>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Fat / amino</div>
      <div>
        <span class="avg week"><?= $this->avg['fat']['week'] ?></span>
        <span class="avg 15days" style="display: none;"><?= $this->avg['fat']['15days'] ?></span>
        <span class="avg 30days" style="display: none;"><?= $this->avg['fat']['30days'] ?></span>
        /
        <span class="avg week"><?= $this->avg['amino']['week'] ?></span>
        <span class="avg 15days" style="display: none;"><?= $this->avg['amino']['15days'] ?></span>
        <span class="avg 30days" style="display: none;"><?= $this->avg['amino']['30days'] ?></span>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Carbs</div>
      <div>
        <span class="avg week"><?= $this->avg['carbs']['week'] ?></span>
        <span class="avg 15days" style="display: none;"><?= $this->avg['carbs']['15days'] ?></span>
        <span class="avg 30days" style="display: none;"><?= $this->avg['carbs']['30days'] ?></span>
      </div>
    </li>
    <!-- TASK: fibre -->
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Salt</div>
      <div>
        <span class="avg week"><?= $this->avg['salt']['week'] ?></span>
        <span class="avg 15days" style="display: none;"><?= $this->avg['salt']['15days'] ?></span>
        <span class="avg 30days" style="display: none;"><?= $this->avg['salt']['30days'] ?></span>
      </div>
    </li>
  </ul>
  <div class="list-group mt-2">

    <!-- ios like design -->

    <?php
    
    $i = 0;
    foreach( $this->lastDaysView->all() as $day => $sums ):
      $i++;  if( $i == 30 )  break; ?>

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >
        <?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
        <small class="text-body-secondary mb-1">
          <b><?= $weekdays[ date('D', strtotime($day))] ?></b>&nbsp;<?= $day ?>
        </small>
        <small class="text-body-secondary mb-1">
          <b><?= $sums['price'] ?> <?= settings::get('currencySymbol') ?></b>
        </small>
      </li>

      <li class="list-group-item">
        <div class="row">
          <div class="col-6">
            <span><?= $sums['fat'] ?> / <?= $sums['carbs'] ?> g</span>
          </div>
          <div class="col-6">
            <span><?= $sums['calories'] ?> kcal</span>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <span><?= $sums['amino'] ?> g</span>
          </div>
          <div class="col-6">
            <span><?= $sums['salt'] ?> g</span>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </div>
</div>
