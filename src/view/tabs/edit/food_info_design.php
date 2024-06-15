<?php

// Can you make a PHP code that prints the yml sample as nice looking bootstrap 5.3 html?
// Some fields are highlighted as (required). All left fields may be missing in the data.

// - instead of "bio: true", vegan: true, and the values in "misc" we should print
//   a line with badges like (bio) (vegan) (NutriScore: A)
// - ignore the usedAmounts field

// The yml sample

$currency = '€';
$key = 'My food S Bio';
$id  = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $key));  // TASK: maybe we need prefix this so that no Ids get confused?
$data = [
  'productName' => '...',
  'vendor' => 'My vendor',
  'url' => '...',
  'acceptable' => 'less',
  'comment' => 'My comment My comment My comment My comment My comment My comment My comment My comment My comment My comment My comment',
  'bio' => true,
  'vegan' => false,
  'misc' => ['NutriScore' => 'A'],
  'ingredients' => 'some long text some long text some long text some long text some long text some long text some long text some long text',
  'origin' => '...',
  'cookingInstrutions' => 'First ...',
  'price' => 1.00,
  'weight' => '100g',
  'pieces' => 6,
  'calories' => 100,
  'nutritionalValues' => [
    'fat' => 100,
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
  'sources' => 'Macro nutrients: web|pack (information on packaging may differ slightly), nutrients: ..., price: ...',
  'lastUpd' => '2024-02-18',
  'lastPriceUpd' => '2024-03-23',
];

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../../../lib/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../../../lib/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <title>Product Info</title>
</head>
<body>
  <div class="container mt-5">
    
    <h6 class="mb-1 fw-bold d-flex justify-content-between align-items-center">
      <span><?= htmlspecialchars($key) ?> (<?= htmlspecialchars($data['vendor']) ?>)</span>
      <i class="bi bi-pencil-square text-black"></i>
    </h6>
    
    <?php
    
    if( ! empty($data['acceptable'])):
    
      $color = $data['acceptable'] == 'less' ? 'danger' : 'warning';
      $text  = $data['acceptable'] == 'less' ? 'less acceptable' : 'acceptable occasionally';
    ?>
      <span class="badge bg-<?= $color ?>"><?= $text ?></span>
    <?php endif; ?>
    <?php if( ! empty($data['bio']) && $data['bio']): ?>
      <span class="badge bg-success">bio</span>
    <?php endif; ?>
    <?php if( ! empty($data['vegan']) && $data['vegan']): ?>
      <span class="badge bg-success">vegan</span>
    <?php endif; ?>
    <?php if( ! empty($data['misc'])): ?>
      <?php foreach( $data['misc'] as $key => $value): ?>
        <span class="badge bg-info"><?= $key ?>: <?= $value ?></span>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if( ! empty($data['comment'])): ?>
      <div class="mt-2 p-2 small" style="background-color: #e0e0e0 !important;">
        <?= htmlspecialchars($data['comment']) ?>
      </div>
    <?php endif; ?>

    <table class="table table-sm table-bordered">
      <tbody>
        <?php if( ! empty($data['ingredients'])): ?>
          <tr>
            <th>Ingredients</th>
            <td>
              <a data-bs-toggle="collapse" href="#<?= $id ?>IngrCollapse" role="button">
                <span class="badge bg-secondary">show</span>
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
        <?php if( ! empty($data['url'])): ?>  <!-- TASK: merge somewhere -->
          <tr>
            <th>URL</th>
            <td>
              <a href="<?= $data['url'] ?>">URL</a>
            </td>
          </tr>
        <?php endif; ?>
        <tr>
          <th>Price</th>
          <td>
            <?= $currency ?><?= $data['price'] ?>
            <span class="text-secondary">(<?=$data['lastPriceUpd'] ?>)</span>
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

    <p style="font-size: .75em;">
      <?= str_replace(',', '<br>', htmlspecialchars($data['sources'])) ?><br>
      Last Update: <?= $data['lastUpd'] ?>
    </p>

    <?php if( ! empty($data['cookingInstrutions'])): ?>
      <div class="p-2 small" style="background-color: #e0e0e0 !important;">
        <b>Cooking instructions</b><br>
        <br>
        <?= $data['cookingInstrutions'] ?>
      </div>
    <?php endif; ?>

    <!-- TASK: add a collapsible -->

    <ul class="list-group mt-3">

        <li class = "list-group-item d-flex justify-content-between align-items-center"
            style = "background-color: #e0e0e0;"
        >
          <span>Calories</span>
          <span><?= $data['calories'] ?></span>
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

        $nutrientsShort = [
          'nutritionalValues' => 'nutriVal',  // TASK: use from controller
          'fattyAcids'        => 'fat',
          'aminoAcids'        => 'amino',
          'vitamins'          => 'vit',
          'minerals'          => 'min',
          'secondary'         => 'sec'
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

  <script src="../../../lib/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
