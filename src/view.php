<?php

use Symfony\Component\Yaml\Yaml;
// use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/parse.php';


$foodsTxt = file_get_contents('data/foods.yml');
$foodsDef = Yaml::parse( $foodsTxt );

// $foods = $foodsDef;  // remove in new version

// Make food list with amounts

$foods = [];

foreach( $foodsDef as $food => $entry )
{
  if( $entry['packaging'] === 'pack')
  {
    $usedAmounts = $entry['usedAmounts'] ?? ['1/4' => 1/4, '1/3' => 1/3, '1/2' => 1/2, '2/3' => 2/3, '3/4' => 3/4, '1' => 1];

    foreach( $usedAmounts as $frac => $multipl )
    {
      if( is_string($multipl))  // calc if loaded from yml cause string
        eval("\$multipl = $data[$frac];");

      $foods["$food $frac"] = [
        'weight'   => round( $entry['weight']   * $multipl, 1),
        'calories' => round( $entry['calories'] * $multipl, 1),
        'amino'    => round( $entry['amino']    * $multipl, 1),
        'salt'     => round( $entry['salt']     * $multipl, 1)
      ];
    }
  }
  elseif( $entry['packaging'] === 'pieces')
  {
    $usedAmounts = $entry['usedAmounts'] ?? [1, 2, 3];

    foreach( $usedAmounts as $amount )
      $foods["$food $amount"] = [
        'weight'   => round(( $entry['weight']   / $entry['quantity'] ) * $amount, 1),
        'calories' => round(( $entry['calories'] / $entry['quantity'] ) * $amount, 1),
        'amino'    => round(( $entry['amino']    / $entry['quantity'] ) * $amount, 1),
        'salt'     => round(( $entry['salt']     / $entry['quantity'] ) * $amount, 1)
      ];
  }
  else  // single piece
  {
    $foods[$food] = [
      'weight'   => $entry['weight'],
      'calories' => $entry['calories'],
      'amino'    => $entry['amino'],
      'salt'     => $entry['salt']
    ];
  }
}

// This day

// $file = 'data/days/' . date('Y-m-d') . '.tsv';
// $dayEntriesTxt = file_exists($file) ? trim( file_get_contents($file)) : '';
$dayEntriesTxt = trim( @file_get_contents('data/days/' . date('Y-m-d') . '.tsv') ?: '');
$dayEntries    = parse($dayEntriesTxt);

$dayCaloriesSum = ! $dayEntries ? 0 : array_sum( array_column( $dayEntries, 1));
$dayAminoSum    = ! $dayEntries ? 0 : array_sum( array_column( $dayEntries, 2));
$daySaltSum     = ! $dayEntries ? 0 : array_sum( array_column( $dayEntries, 3));

// All days

$lastDaysSums = [];

foreach( scandir('data/days', SCANDIR_SORT_DESCENDING) as $file)
{
  if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
    continue;

  $dat     = pathinfo($file, PATHINFO_FILENAME);
  $entries = parse( file_get_contents("data/days/$file"));

  $lastDaysSums[$dat] = [
    'caloriesSum' => ! $entries ? 0 : array_sum( array_column($entries, 1)),
    'aminoSum'    => ! $entries ? 0 : array_sum( array_column($entries, 2)),
    'saltSum'     => ! $entries ? 0 : array_sum( array_column($entries, 3))
  ];
}

?>

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
        ><?= $dayEntriesTxt ?></textarea>

        <div class="row mt-2">  <!-- text-start text-left same for diff bs versions -->
          <div class="col ml-3 pl-1 pr-1 bg-secondary text-start text-left small">
            kcal
          </div>
          <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
            <div class="align-split small"><span>Fat</span> <span>g</span></div>  <!-- align-split ai made class -->
          </div>
          <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
            <div class="align-split small"><span>Pro</span> <span>g</span></div>
          </div>
          <div class="col pl-1 mr-3 pr-1 border-left bg-secondary text-start text-left">
            <div class="align-split small"><span>Salt</span> <span>g</span></div>
          </div>
          <div class="col text-end text-right" style="max-height: 20px;">
            <button class="btn btn-sm btn-light" onclick="saveManualInput()">Save</button>
          </div>
        </div>
        <div class="row">
          <div class="col ml-3 pl-1 pr-1 text-start text-left" id="caloriesSum"><?= $dayCaloriesSum ?></div>
          <div class="col pl-1 pr-1 text-end text-right" id="aminoSum">0</div>
          <div class="col pl-1 pr-1 text-end text-right" id="aminoSum"><?= $dayAminoSum ?></div>
          <div class="col pl-1 mr-3 pr-1 text-end text-right" id="saltSum"><?= $daySaltSum ?></div>
          <div class="col text-end text-right">
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
          <?php foreach( $foods as $food => $entry): ?>
            <div class="food-item" onclick="addFood('<?= $food ?>', <?= $entry['calories'] ?>, <?= $entry['amino'] ?>, <?= $entry['salt'] ?>)">
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

        <?php foreach( $lastDaysSums as $day => $sums): ?>
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
        ><?= $foodsTxt ?></textarea>

        <button id="saveFoodsBtn" onclick="saveFoods(event)" class="btn btn-sm btn-primary mt-2">Save</button>
        <span   id="foodsUIMsg"></span>

        <a href="https://bootstrap.build/license">BS theme by bootstrap.build</a>

      </div>
    </div>
  </div>

</div>

<script>

  let dayEntries = [
    <?php foreach( $dayEntries as $entry): ?>
      {food: '<?= $entry[0] ?>', calories: <?= $entry[1] ?>, amino: <?= $entry[2] ?>, salt: <?= $entry[3] ?>},
    <?php endforeach; ?>
  ]

</script>
