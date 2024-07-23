<div class="scrollable-list border-0 mt-3">

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

    <?php foreach( $this->lastDaysView->all() as $day => $sums ): ?>

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >

        <!-- TASK: maybe use some ios like save some space (needs spec layout for larger) -->
        <!-- <div class="col-12 col-md-6 col-xxl-4 mt-2"> -->

        <?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
        <small class="text-body-secondary mb-1">
          <b><?= $weekdays[ date('D', strtotime($day))] ?></b>&nbsp;<?= $day ?>
        </small>
        <small class="text-body-secondary mb-1">
          <b><?= $sums['Price'] ?></b>
        </small>
      </li>
<!--
      <?php foreach( $sums as $label => $sum): ?>
      <?php   if( $label == 'Price')  continue; ?>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><?= $label ?></span>
          <span><?= $sum ?></span>
        </li>
      <?php endforeach; ?>
-->
      <li class="list-group-item">
        <div class="row">
          <div class="col-6">
            <span><?= $sums['Fat'] ?> / <?= $sums['Carbs'] ?> g</span>
          </div>
          <div class="col-6">
            <span><?= $sums['Calories'] ?></span>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <span><?= $sums['Amino acids'] ?></span>
          </div>
          <div class="col-6">
            <span><?= $sums['Salt'] ?></span>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </div>
</div>
