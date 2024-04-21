<div class="row">
  <div class="col scrollable-list">

    <!-- using BS is easier here than aligning tsv -->
    <!-- (all alternatives seen 2403) https://getbootstrap.com/docs/5.3/components/list-group/#custom-content) -->

    <?php foreach( $this->model->lastDaysSums as $day => $sums): ?>
      <div class="list-group">
        <a href="#" class="list-group-item list-group-item-action p-2">
          <div class="d-flex w-100 justify-content-between">
            <?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
            <small class="text-body-secondary mb-1"><b><?= $weekdays[ date('D', strtotime($day))] ?></b>
            &nbsp;<?= $day ?></small>
            <small class="text-body-secondary mb-1"><b>1 EUR</b></small>
          </div>
          <div class="row small">
            <div class="col-3"><?= $sums['caloriesSum'] ?> kcal</div>
            <div class="col-3"><?= $sums['fatSum'] ?> g</div>
            <div class="col-3"><?= $sums['aminoSum'] ?> g</div>
            <div class="col-3"><?= $sums['saltSum'] ?> g</div>
          </div>
          <!-- <small class="text-body-secondary">And some muted small print.</small> -->
        </a>
      </div>
    <?php endforeach; ?>

  </div>
</div>
