<div class="scrollable-list border-0 mt-3">
<!--
  <ul class="list-group">
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Price avg</div>
      <div>
        < ?= number_format( $this->priceAvg, 2) ?>
        < ?= settings::get('currencySymbol') ?>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Week avg</div>
      <div>(maybe)</div>
    </li>
  </ul>
-->
  <div class="dropdown">
    <button class="btn btn-secondary dropdown-toggle px-2 py-0 small" type="button" data-bs-toggle="dropdown">
      Last week
    </button>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item small" href="#">Last week</a></li>
      <li><a class="dropdown-item small" href="#">Last month</a></li>
    </ul>
  </div>

  <ul class="list-group mt-2">
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Price</div>
      <div>
        <?= number_format( $this->priceAvg, 2) ?>
        <?= settings::get('currencySymbol') ?>
      </div>
    </li>
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div>Calories</div>
      <div>
        add some ...
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
          <b><?= $sums['Price'] ?> <?= settings::get('currencySymbol') ?></b>
        </small>
      </li>

      <li class="list-group-item">
        <div class="row">
          <div class="col-6">
            <span><?= $sums['Fat'] ?> / <?= $sums['Carbs'] ?> g</span>
          </div>
          <div class="col-6">
            <span><?= $sums['Calories'] ?> kcal</span>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <span><?= $sums['Amino acids'] ?> g</span>
          </div>
          <div class="col-6">
            <span><?= $sums['Salt'] ?> g</span>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </div>
</div>
