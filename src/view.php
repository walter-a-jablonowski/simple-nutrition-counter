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

<div class="tab-content mt-3">

  <!-- Edit Tab -->

  <div class="tab-pane fade show active" id="inpPane" role="tabpanel">
    <div class="row">
      <div class="col">

        <!-- Day entries -->

        <textarea id="dayEntries" class="form-control" wrap="off" style="font-family: monospace;" rows="5"
        ><?= $this->model->dayEntriesTxt ?></textarea>

        <!-- full ui (#code/advancedDayEntries) -->
<!--
        <div class="scrollable-list">
          <ul id="dayEntries" class="list-group">
            < ?php for( $i=0; $i < 4; $i++): ?>
            <!-- < ?php foreach( $this->model->... ): ?> -- >
              <li class   = "food-item p-0 list-group-item d-flex justify-content-between align-items-center"
                  onclick = ""
                  data-type      = "food"
                  data-weight    = ""
                  data-calories  = ""    v make own function
                  data-nutrients = "< ?= html_encode( json_encode( $entry['nutrients'])) ?>"
              >
                <span>
                  <span class="handle bi bi-grip-vertical"></span>
                  < ?= "UI demo food $i" ?>
                </span>
                <div>
                  <i class="bi bi-pencil-square btn px-0"></i>
                  <button type="button" class="btn">  <!-- maybe add the (-) or make btn in dlg -- >
                    <i class="bi bi-dash-circle"></i>
                  </button>
                </div>
              </li>
            < ?php endfor; ?>
          </ul>
        </div>
-->

        <!-- Quick summary -->
        
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
          <div id="h2oSum"      class="col ml-2 pl-1 pr-1 text-start text-left">0</div>
          <div id="caloriesSum" class="col pl-1 pr-1 text-start text-left">0</div>
          <div id="fatSum"      class="col pl-1 pr-1 text-end text-right">0</div>
          <div id="aminoSum"    class="col pl-1 pr-1 text-end text-right">0</div>
          <div id="saltSum"     class="col pl-1 pr-1 text-end text-right">0</div>
          <div id="priceSum"    class="col pl-1 pr-1 text-end text-right">0</div>
          <div class="col mr-2 pl-1 text-end text-right">
            &nbsp;
          </div>
        </div>

      </div>
    </div>

    <!-- Food list -->

    <div class="row mt-2">
      <div class="col scrollable-list">

        <ul id="foodList" class="list-group">
          <?php foreach( $this->model->foods as $food => $entry): ?>
            <li class   = "food-item p-1 list-group-item d-flex justify-content-between align-items-center"
                onclick = "foodsCrl.foodItemClick(event)"
                data-food      = "<?= $food ?>"
                data-calories  = "<?= $entry['calories'] ?>"
                data-nutrients = "<?= htmlspecialchars( json_encode( $entry['nutrients'])) ?>"
            >
              <?= $food ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <!-- new -->
<!--
        <div id="foodList" class="row">

          <!-- static #code/staticListEntries -- >

          <div class="col">

            <div class="row">
              <div class="col-12" onclick = "...">
                Expired ...
              </div>
            </div>

          </div>

          <?php foreach( $layout as $group => $lines ): ?>
            <div class="col">

              <?php if( trim($group) ): ?>
                <div class="row">
                  <div class="col-12 p-1 small">
                    <?= trim($group) ?>
                  </div>
                </div>
              <?php endif; ?>

              <?php foreach( $lines as $btns ):

                $done = [];
              ?>
                <div class="row">
                  <?php foreach( $btns as $btn ):

                    $done[] = $btn;
                  ?>
                    <div class="food-item col p-2" onclick = "foodsCrl.foodItemClick(event)"
                      data-food      = "<?= $food ?>"
                      data-calories  = "<?= $entry['calories'] ?>"
                      data-nutrients = "<?= htmlspecialchars( json_encode( $entry['nutrients'])) ?>"
                    >
                      <?= trim($btn) ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>

            </div>
          <?php endforeach; ?>
        </div>
-->
      </div>
    </div>
  </div>

  <!-- Day tab (summary) -->

  <div class="tab-pane fade" id="dayPane" role="tabpanel">

    this is an advanced feature

    <div class="scrollable-list">
      <div id="">
        <?php for( $i=0; $i < 4; $i++): ?>
          <div>Substance <?= $i ?></div>  <!-- simple for now -->
          <div class="d-flex align-items-center mb-2">
            <div class="progress w-100" role="progressbar" style="margin-right: 20px;">
              <div id="<?= $i ?>ProgressBar" class="progress-bar bg-success" style="width: 80%;">80%</div>
            </div>
            <span id="<?= $i ?>ProgressLabel">100/500</span>
          </div>
        <?php endfor; ?>
      </div>
    </div>

  </div>

  <!-- Last days tab -->

  <div class="tab-pane fade" id="lastDaysPane" role="tabpanel">
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
              <!-- (TASK) we could add some #code/collapse here -->
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
      <div class="col">

        <textarea id="foods" class="form-control" wrap="off" style="font-family: monospace; font-size: 15px;" rows="18"
        ><?= $this->model->foodsTxt ?></textarea>

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

  dayEntries = [  // will be replaced by #code/advancedData
    <?php foreach( $this->model->dayEntries as $entry): ?>
      {food: '<?= $entry[0] ?>', calories: <?= $entry[1] ?>, fat: <?= $entry[2] ?>, amino: <?= $entry[3] ?>, salt: <?= $entry[4] ?>},
    <?php endforeach; ?>
  ]

  foodsCrl = new FoodsEventController()
  foodsCrl.updSums()
})

</script>
