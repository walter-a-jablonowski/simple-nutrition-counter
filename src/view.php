<!-- Tabs -->

<ul class="nav nav-tabs" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link active py-1 px-2 small" data-bs-toggle="tab" href="#inpPane" role="tab">This day</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#dayPane" role="tab">Summary</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#lastDaysPane" role="tab">Last days</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#foodsPane" role="tab">Foods</a>
  </li>
</ul>

<!-- Tab Content -->

<div class="tab-content mt-4">

  <!-- Edit Tab -->

  <div class="tab-pane fade show active" id="inpPane" role="tabpanel">
    <div class="row">
      <div class="col-md-6">

        <textarea id="dayEntries" class="form-control" wrap="off" style="font-family: monospace;" rows="5"
        ><?= $this->data->dayEntriesTxt ?></textarea>

        <div class="row mt-2">  <!-- text-start text-left same for diff bs versions -->
          <div class="col ml-2 pl-1 pr-1 bg-secondary text-start text-left small">
            <b>H2O</b>
          </div>
          <div class="col pl-1 pr-1 bg-secondary text-start text-left small">
            kcal
          </div>
          <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
            <div class="align-split small"><span>Fat</span> <span>g</span></div>  <!-- align-split ai made class -->
          </div>
          <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
            <div class="align-split small"><span>Pro</span> <span>g</span></div>
          </div>
          <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
            <div class="align-split small"><span>Salt</span> <span>g</span></div>
          </div>
          <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left small">
            <b>Price</b>
          </div>
          <div class="col mr-2 pl-1 text-end text-right" style="max-height: 20px;">
            <button class="btn btn-sm btn-light" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
              Save
            </button>
          </div>
        </div>
        <div class="row">
          <div id="h2oSum" class="col ml-2 pl-1 pr-1 text-start text-left">0</div>
          <div id="caloriesSum" class="col pl-1 pr-1 text-start text-left">0</div>
          <div id="fatSum" class="col pl-1 pr-1 text-end text-right">0</div>
          <div id="aminoSum" class="col pl-1 pr-1 text-end text-right">0</div>
          <div id="saltSum" class="col pl-1 pr-1 text-end text-right">0</div>
          <div id="priceSum" class="col pl-1 pr-1 text-end text-right">0</div>
          <div class="col mr-2 pl-1 text-end text-right">
            &nbsp;
          </div>
        </div>

      </div>
    </div>

    <div class="row mt-2">
      <div class="col-md-12">

        <div id="foodList" class="scrollable-list">
<!--
          <div class="food-item" onclick="">
            Enter manually ...
          </div>
-->
          <?php foreach( $this->data->foods as $food => $entry): ?>
            <div class="food-item" onclick="foodsCrl.foodItemClick(event)"
                 data-food      = "<?= $food ?>"
                 data-calories  = "<?= $entry['calories'] ?>"
                 data-nutrients = "<?= htmlspecialchars( json_encode( $entry['nutrients'])) ?>"
            >
              <?= $food ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Day tab (summary) -->

  <div class="tab-pane fade" id="dayPane" role="tabpanel">

    this is an advanced feature

    <div class="scrollable-list">
      <div class="d-flex align-items-center mb-2">
        <div class="progress w-100" role="progressbar" style="margin-right: 20px;">
          <div id="caloriesProgressBar" class="progress-bar bg-success" style="width: 80%;">80%</div>
        </div>
        <span id="caloriesProgressLabel">100/500</span>
      </div>
      <div class="d-flex align-items-center mb-2">
        <div class="progress w-100" role="progressbar" style="margin-right: 20px;">
          <div id="fatProgressBar" class="progress-bar bg-success" style="width: 80%;">80%</div>
        </div>
        <span id="fatProgressLabel">100/500</span>
      </div>
    </div>

  </div>

  <!-- Last days tab -->

  <div class="tab-pane fade" id="lastDaysPane" role="tabpanel">
    <div class="row">
      <div class="col-md-6 scrollable-list">

        <!-- using BS is easier here than aligning tsv -->
        <!-- (all alternatives seen 2403) https://getbootstrap.com/docs/5.3/components/list-group/#custom-content) -->

        <?php foreach( $this->data->lastDaysSums as $day => $sums): ?>
          <div class="list-group">
            <a href="#" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <?php $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su']; ?>
                <small class="text-body-secondary mb-1"><b><?= $weekdays[ date('D', strtotime($day))] ?></b>
                &nbsp;<?= $day ?></small>
                <!-- just add second elem (aligned right) -->
              </div>
              <div class="row">
                <div class="col"><?= $sums['caloriesSum'] ?></div>
                <div class="col"><?= $sums['fatSum'] ?></div>
                <div class="col"><?= $sums['aminoSum'] ?></div>
                <div class="col"><?= $sums['saltSum'] ?></div>
              </div>
              <!-- (TASK) we could add some collapse here -->
              <!-- <small class="text-body-secondary">And some muted small print.</small> -->
            </a>
          </div>
        <?php endforeach; ?>

      </div>
    </div>
  </div>

  <!-- Foods Tab -->

  <div class="tab-pane fade" id="foodsPane" role="tabpanel">
    <div class="row">
      <div class="col-md-6">

        <textarea id="foods" class="form-control" wrap="off" style="font-family: monospace;" rows="18"
        ><?= $this->data->foodsTxt ?></textarea>

        <button onclick="foodsCrl.saveFoodsBtnClick(event)" class="btn btn-sm btn-primary mt-2">Save</button>
        <span id="foodsUIMsg"></span>

      </div>
    </div>
  </div>

</div>

<script src="controller.js"></script>
<script>

var dayEntries, foodsCrl

ready( function() {

  dayEntries = [
    <?php foreach( $this->data->dayEntries as $entry): ?>  // TASK: nutrients in sub list
      {food: '<?= $entry[0] ?>', calories: <?= $entry[1] ?>, fat: <?= $entry[2] ?>, amino: <?= $entry[3] ?>, salt: <?= $entry[4] ?>},
    <?php endforeach; ?>
  ]

  foodsCrl = new FoodsEventController()
  foodsCrl.updSums()
})

</script>
