<?php

// Can you make a PHP code that prints the yml sample as nice looking bootstrap 5.3 html?
// Some fields are highlighted as (required). All left fields may be missing in the data.

// - instead of "bio: true", vegan: true, and the values in "misc" we should print
//   a line with badges like (bio) (vegan) (NutriScore: A)
// - ignore the usedAmounts field

// The yml sample

$nutrientsShort = [
  'nutritionalValues' => 'nutriVal',  // TASK: use from controller
  'fattyAcids'        => 'fat',
  'aminoAcids'        => 'amino',
  'vitamins'          => 'vit',
  'minerals'          => 'min',
  'secondary'         => 'sec'
];

$currency = '€';  // TASK: from settings
$key = 'My food S Bio';
$id  = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $key));  // TASK: maybe we need prefix this so that no Ids get confused?
$data = [
  'productName' => '...',
  'vendor' => 'Aldi',
  'url' => '...',
  'acceptable' => 'less',
  'comment' => 'My comment My comment My comment My comment My comment My comment My comment My comment My comment My comment My comment',
  'attribs' => ['bio' => true, 'vegan' => false, 'NutriScore' => 'A'],
  'ingredients' => 'some long Weizen text some long text some long text some long text some long text some long text some long text some long text',
  'origin' => '...',
  'cookingInstrutions' => 'First ...',
  'price' => 1.00,
  'weight' => '100g',
  'pieces' => 6,
  'calories' => 100,
  'nutritionalValues' => [
    'fat' => 101,
    'saturatedFat' => null,
    'monoUnsaturated' => null,
    'polyUnsaturated' => null,
    'carbs' => null,
    'sugar' => null,
    'sugarAlcohol' => null,
    'fibre' => null,
    'amino' => null,
    'salt' => 1.0,
  ],
  'fattyAcids' => [],
  'aminoAcids' => [],
  'vitamins' => [],
  'minerals' => [
    'calcium' => 1,
  ],
  'secondary' => [],
  'sources' => ['nutriVal' => 'web|pack (information on packaging may differ slightly)', 'nutrients' => '...', 'price' => '...'],
  'lastUpd' => '2024-02-18',
  'lastPriceUpd' => '2024-03-23',
];

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="lib/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="lib/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <title>Food info</title>
</head>
<body>
  <div class="container mt-5">
    
    <!-- Headline -->
    <!-- TASK: (advanced) sometimes it isn't the vendor url, no better place for url for now -->

    <h6 class="mb-1 fw-bold d-flex justify-content-between align-items-center">
      <span>
        <?= htmlspecialchars($key) ?>
        <?php if( ! empty($data['vendor']) || ! empty($data['url'])): ?>
          <span class="fw-normal small">
            <?php if( ! empty($data['vendor']) && ! empty($data['url'])): ?>
              (<a href="<?= $data['url'] ?>" target="_blank" class="text-decoration-none"><?= $data['vendor'] ?></a>)
            <?php elseif( empty($data['vendor']) && ! empty($data['url'])): ?>
              (<a href="<?= $data['url'] ?>" target="_blank" class="text-decoration-none">url</a>)
            <?php elseif( ! empty($data['vendor']) && empty($data['url'])): ?>
              (<?= $data['vendor'] ?>)
            <?php endif; ?>
          </span>
        <?php endif; ?>
      </span>
      <?php if( $this->devMode ): ?>
        <i class="bi bi-pencil-square text-black"></i>  <!-- TASK: (advanced) or make all editable on typ -->
      <?php endif; ?>
    </h6>

    <!-- Badges -->

    <?php if( ! empty($data['acceptable'])): ?>
      <span class="badge bg-<?= $this->iif( $data['acceptable'] == 'less', 'danger', 'warning') ?>">
        <?= $this->iif( $data['acceptable'] == 'less', 'less good', 'occasionally') ?>
      </span>
    <?php endif; ?>
    <span class="badge bg-<?= $this->iif( ! empty($data['attribs']['bio']), 'success', 'secondary') ?>">
      <?= $this->iif( ! empty($data['attribs']['bio']), 'bio', '<s>bio</s>') ?>  <!-- TASK: non working -->
    </span>
    <span class="badge bg-<?= $this->iif( ! empty($data['attribs']['vegan']), 'success', 'secondary') ?>">
      <?= $this->iif( ! empty($data['attribs']['vegan']), 'vegan', '<s>vegan</s>') ?>
    </span>
    <?php if( ! empty($data['attribs']['NutriScore'])): ?>
      <span class="badge bg-info">NutriScore</span>
    <?php endif; ?>

    <!-- High fat ... -->
    <!-- TASK: add -->

    <?php if( $data['nutritionalValues']['fat'] > config::get('highIntake.fat')): ?>
      <span class="badge bg-danger">fatty</span>
    <?php endif; ?>

    <!-- Gluten and similar from ingredients list -->
    <!-- TASK: maybe also add a flag gluten: true in food data where you can add it manually -->

    <?php if( ! empty($data['ingredients'])): ?>
      <?php foreach( config::get('substances.gluten') as $s ): ?>
        <?php if( stripos( $data['ingredients'], $s) !== false): ?>
          <span class="badge bg-danger">gluten</span>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php if( ! empty($data['ingredients'])): ?>
      <?php foreach( config::get('substances.lactose') as $s ): ?>
        <?php if( stripos( $data['ingredients'], $s) !== false): ?>
          <span class="badge bg-danger">lactose</span>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Comment -->

    <?php if( ! empty($data['comment'])): ?>
      <div class="mt-2 p-2 small" style="background-color: #e0e0e0 !important;">
        <?= htmlspecialchars($data['comment']) ?>
      </div>
    <?php endif; ?>
    
    <!-- Table -->

    <table class="table table-sm table-bordered">
      <tbody>
        <?php if( ! empty($data['ingredients'])): ?>
          <tr>
            <th>Ingredients</th>
            <td>
              <a data-bs-toggle="collapse" href="#<?= $id ?>IngrCollapse" class="text-decoration-none" role="button">
                <span class="text-secondary small">show</span>
              </a>
            </td>
          </tr>
          <tr id="<?= $id ?>IngrCollapse" class="collapse">
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
            <?= $currency ?><?= $data['price'] ?>
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
    <!-- TASK: collapse sources, leave upd (right align)? -->
    <!-- TASK: rm the borcer -->

    <table class="table" style="font-size: .75em;">
      <tbody>
        <tr>
          <td colspan="2" class="p-0 fw-bold">Data sources</td>
        </tr>
        <?php
          $headlines = ['nutriVal' => 'Nutri values', 'nutrients' => 'Nutrients', 'price' => 'Price'];
        ?>
        <?php foreach( $data['sources'] as $key => $source ): ?>
          <tr>
            <td class="p-0"><?= $headlines[ $key ] ?></td>
            <td class="p-0"><?= $source ?></td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="2" class="p-0">Last update: <?= $data['lastUpd'] ?></td>
        </tr>
      </tbody>
    </table>

    <!-- Last update -->

    <!-- TASK -->

    <!-- Cooking instructions -->

    <?php if( ! empty($data['cookingInstrutions'])): ?>
      <div class="p-2 small" style="background-color: #e0e0e0 !important;">
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

          $collapseId = $id . ucwords( $nutrientsShort[$group]) . 'Collapse';
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
  </div>

  &nbsp;  <!-- spacer -->

  <script src="lib/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
