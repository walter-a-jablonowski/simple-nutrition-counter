<ul class="list-group">
  <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
    <div>Price avg</div>
    <div>
      <?= number_format( $this->priceAvg, 2) ?>
      <?= $this->settings->get('currencySymbol') ?>
    </div>
  </li>
  <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
    <div>Week avg</div>
    <div>(maybe)</div>
  </li>
</ul>

<!--
<div class="row ps-2 mt-4 mb-2 small">
  <div class="col-2 fw-bold">kcal</div>
  <div class="col-2 fw-bold">Carbs</div>
  <div class="col-2 fw-bold">Fat</div>
  <div class="col-2 fw-bold">Amino</div>
  <div class="col-2 fw-bold">Salt</div>
</div>
-->

<div class="scrollable-list">
<!--
  <div class="list-group">
    < ?php foreach( $this->lastDaysSums as $day => $sums): ?>
      <a href="#" class="list-group-item list-group-item-action p-2">
        <div class="d-flex w-100 justify-content-between">
          < ?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
          <small class="text-body-secondary mb-1"><b>< ?= $weekdays[ date('D', strtotime($day))] ?></b>
          &nbsp;< ?= $day ?></small>
          <small class="text-body-secondary mb-1"><b>< ?= $sums['priceSum'] ?> EUR</b></small>
        </div>
        <div class="row small">
          <div class="col-2">< ?= $sums['caloriesSum'] ?> kcal</div>
          <div class="col-2">< ?= $sums['carbsSum'] ?> g</div>
          <div class="col-2">< ?= $sums['fatSum'] ?> g</div>
          <div class="col-2">< ?= $sums['aminoSum'] ?> g</div>
          <div class="col-2">< ?= $sums['saltSum'] ?> g</div>
        </div>
      </a>
    < ?php endforeach; ?>
  </div>
-->
  <div class="list-group">

    <?php foreach( $this->lastDaysSums as $day => $sums): ?>

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >
        <?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
        <small class="text-body-secondary mb-1"><b><?= $weekdays[ date('D', strtotime($day))] ?></b>
        &nbsp;<?= $day ?></small>
        <small class="text-body-secondary mb-1"><b><?= $sums['priceSum'] ?> EUR</b></small>
      </li>

      <?php foreach(['caloriesSum', 'fatSum', 'carbsSum', 'aminoSum', 'saltSum'] as $type): ?>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><?= $type ?></span>
          <span><?= $sums[$type] ?></span>  <!-- TASK: name, kcal or g -->
        </li>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
</div>
