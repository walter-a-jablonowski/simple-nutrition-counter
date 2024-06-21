<?php

$nutrientsShort = [
  'nutritionalValues' => 'nutriVal',  // TASK: use from controller
  'fattyAcids'        => 'fat',
  'aminoAcids'        => 'amino',
  'vitamins'          => 'vit',
  'minerals'          => 'min',
  'secondary'         => 'sec'
];

?>
<!-- Badges -->
<!-- TASK: add { oekotest: "sehr gut" } -->

<?php if( ! empty($data['acceptable'])): ?>
  <span class="badge bg-<?= $this->iif( $data['acceptable'] == 'less', 'danger', 'warning') ?>">
    <?= $this->iif( $data['acceptable'] == 'less', 'less good', 'occasionally') ?>
  </span>
<?php endif; ?>
<span class="badge bg-<?= $this->iif( ! empty($data['properties']['bio']), 'success', 'secondary') ?>">
  <?= $this->iif( ! empty($data['properties']['bio']), 'bio', '<s>bio</s>') ?>  <!-- TASK: non working -->
</span>
<span class="badge bg-<?= $this->iif( ! empty($data['properties']['vegan']), 'success', 'secondary') ?>">
  <?= $this->iif( ! empty($data['properties']['vegan']), 'vegan', '<s>vegan</s>') ?>
</span>
<?php if( ! empty($data['properties']['NutriScore'])): ?>
  <span class="badge bg-info">NutriScore</span>
<?php endif; ?>

<!-- High fat ... -->
<!-- TASK: add -->

<?php if( $data['nutritionalValues']['fat'] > config::get('highIntake.fat')): ?>
  <span class="badge bg-danger">fatty</span>
<?php endif; ?>

<!-- Gluten and similar from ingredients list -->
<!-- TASK: maybe also add a flag gluten: true in food data where you can add it manually -->
<!-- TASK: (advanced) add high calcium ... -->

<?php if( ! empty($data['ingredients'])): ?>
  <?php foreach( config::get('substances.gluten') as $s ): ?>
    <?php if( stripos( $data['ingredients'], $s) !== false): ?>
      <span class="badge bg-danger">gluten</span>
    <?php break; endif; ?>
  <?php endforeach; ?>
<?php endif; ?>
<?php if( ! empty($data['ingredients'])): ?>
  <?php foreach( config::get('substances.lactose') as $s ): ?>
    <?php if( stripos( $data['ingredients'], $s) !== false): ?>
      <span class="badge bg-danger">lactose</span>
    <?php break; endif; ?>
  <?php endforeach; ?>
<?php endif; ?>

<!-- Comment -->

<?php if( ! empty($data['comment'])): ?>
  <div class="mt-2 p-2 small" style="background-color: #e0e0e0 !important;">
    <?= htmlspecialchars($data['comment']) ?>
  </div>
<?php endif; ?>

<!-- Table -->

<table class="table table-sm table-bordered m-0">
  <tbody>
    <?php if( ! empty($data['ingredients'])): ?>
      <tr>
        <th>Ingredients</th>
        <td>
          <a data-bs-toggle="collapse" href="#<?= $foodId ?>IngrCollapse" class="text-decoration-none" role="button">
            <span class="text-secondary small">show</span>
          </a>
        </td>
      </tr>
      <tr id="<?= $foodId ?>IngrCollapse" class="collapse">
        <td colspan="2" class="text-wrap" style="white-space: pre-wrap;"><?= htmlspecialchars($data['ingredients']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['origin'])): ?>
      <tr>
        <th>Origin</th>
        <td><?= htmlspecialchars($data['origin']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['productName'])): ?>
      <tr>
        <th>Product name</th>
        <td><?= htmlspecialchars($data['productName']) ?></td>
      </tr>
    <?php endif; ?>
    <tr>
      <th>Price</th>
      <td>
        <?= $this->settings->get('defaultSettings.currency') ?><?= $data['price'] ?>
        <span class="text-secondary small">on <?=$data['lastPriceUpd'] ?></span>
      </td>
    </tr>
    <tr>
      <th>Weight</th>
      <td>
        <?= $data['weight'] ?>&nbsp;
        <?php if( ! empty($data['pieces'])): ?>
          (<?= $data['pieces'] ?> pieces)
        <?php endif; ?>
      </td>
    </tr>
  </tbody>
</table>

<!-- Sources -->

<div class="d-flex justify-content-between align-items-center mt-1">
  <a data-bs-toggle="collapse" href="#<?= $foodId ?>SourcesCollapse" class="text-secondary text-decoration-none" style="font-size: .75em;" role="button">
    &nbsp;Data sources <i class="bi bi-caret-down"></i>
  </a>
  <span class="text-secondary" style="font-size: .75em;">Last update: <?= $data['lastUpd'] ?></span>
</div>
<table id="<?= $foodId ?>SourcesCollapse" class="table collapse" style="font-size: .75em;">
  <tbody>
    <?php foreach( $data['sources'] as $key => $source ): ?>
      <tr>
        <?php $headlines = ['nutriVal' => 'Nutri values', 'nutrients' => 'Nutrients', 'price' => 'Price']; ?>
        <td class="border-0 p-0">&nbsp;<?= $headlines[ $key ] ?></td>
        <td class="border-0 p-0 text-nowrap" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis;">
          <?= $source ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- TASK -->

<!-- Cooking instructions -->

<?php if( ! empty($data['cookingInstrutions'])): ?>
  <div class="mt-3 p-2 small" style="background-color: #e0e0e0 !important;">
    <b>Cooking instructions</b><br>
    <br>
    <?= $data['cookingInstrutions'] ?>
  </div>
<?php endif; ?>

<!-- Foldable -->

<ul class="list-group mt-3">

    <li class = "list-group-item d-flex justify-content-between align-items-center"
        style = "background-color: #e0e0e0;"
    >
      <span>Calories</span>
      <span><?= $data['calories'] ?> kcal</span>
    </li>

  <?php

    $headlines = [
      'nutritionalValues' => 'Nutritional values',
      'fattyAcids' => 'Fatty acids',
      'aminoAcids' => 'Amino acids',
      'vitamins'   => 'Vitamins',
      'minerals'   => 'Minerals',
      'secondary'  => 'Secondary plant substances'
    ];

    foreach(['nutritionalValues', 'fattyAcids', 'aminoAcids', 'vitamins', 'minerals', 'secondary'] as $group):

      $collapseId = $foodId . ucwords( $nutrientsShort[$group]) . 'Collapse';
  ?>

    <?php if( ! empty($data[$group])): ?>

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >
        <?= $headlines[$group] ?>
        <a data-bs-toggle="collapse" href="#<?= $collapseId ?>" class="text-body-secondary" role="button">
          <i class="bi bi-arrow-down-circle"></i>
        </a>
      </li>

      <li id="<?= $collapseId ?>" class="list-group-item collapse">

        <table class="table table-bordered">
          <tbody>

            <?php foreach( $data[$group] as $key => $value): ?>
              <tr>
                <td><?= ucwords( str_replace('_', ' ', $key)) ?></td>
                <td><?= $value ?></td>
              </tr>
            <?php endforeach; ?>

          </tbody>
        </table>
      </li>
    <?php endif; ?>
  <?php endforeach; ?>
</ul>
