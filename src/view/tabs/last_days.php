<div class="scrollable-list border-0 mt-3">
<!-- dorpdown version -->
<!--
  <ul class="list-group mt-2">
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div class="dropdown">
        <button class="btn dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
          <span class="small">Last week</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item small" href="#">Last week</a></li>
          <li><a class="dropdown-item small" href="#">2 weeks</a></li>
          <li><a class="dropdown-item small" href="#">Last month</a></li>
        </ul>
      </div>
      <div>
        < ?= number_format( $this->priceAvg, 2) ?>
        < ?= settings::get('currencySymbol') ?>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Calories</div>
      <div>
        add some ...
      </div>
    </li>
  </ul>
-->
<!-- TASK: MOV maybe we want avg data for nutrients as well -->
  <ul class="list-group mt-2">
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Week avg</div>
      <div>
        <?= $this->priceAvg['week']  ?>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>15 days</div>
      <div>
        <?= $this->priceAvg['15days'] ?>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>30 days</div>
      <div>
        <?= $this->priceAvg['30days'] ?>
      </div>
    </li>
  </ul>
  <div class="list-group mt-2">

    <!-- ios like design -->

    <?php foreach( $this->lastDaysView->all() as $day => $sums ): ?>

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
