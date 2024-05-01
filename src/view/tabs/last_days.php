<div class="row">
  <div class="col scrollable-list">

    <?php foreach( $this->lastDaysSums as $day => $sums): ?>
      <div class="list-group">
        <a href="#" class="list-group-item list-group-item-action p-2">
          <div class="d-flex w-100 justify-content-between">
            <?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
            <small class="text-body-secondary mb-1"><b><?= $weekdays[ date('D', strtotime($day))] ?></b>
            &nbsp;<?= $day ?></small>
            <small class="text-body-secondary mb-1"><b><?= $sums['priceSum'] ?> EUR</b></small>
          </div>
          <div class="row small">
            <div class="col-2"><?= $sums['caloriesSum'] ?> kcal</div>
            <!-- <div class="col-2">< ?= $sums['carbsSum'] ?> g</div> -->
            <div class="col-2"><?= $sums['fatSum'] ?> g</div>
            <div class="col-2"><?= $sums['aminoSum'] ?> g</div>
            <div class="col-2"><?= $sums['saltSum'] ?> g</div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>

  </div>
</div>
