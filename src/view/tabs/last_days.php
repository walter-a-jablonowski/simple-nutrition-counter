<div class="scrollable-list">

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

  <div class="list-group mt-3">

    <?php foreach( $this->lastDaysSums as $day => $sums): ?>

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >

        <!-- TASK: maybe use some ios like save some space -->
        <!-- <div class="col-12 col-md-6 col-xxl-4 mt-2"> -->

        <?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
        <small class="text-body-secondary mb-1"><b><?= $weekdays[ date('D', strtotime($day))] ?></b>
        &nbsp;<?= $day ?></small>
        <small class="text-body-secondary mb-1">
          <b><?= $sums['priceSum'] ?> <?= $this->settings->get('currencySymbol') ?></b>
        </small>
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
