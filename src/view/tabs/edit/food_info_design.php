<?php

// Can you make a PHP code that prints the yml sample as nice looking bootstrap 5.3 html?
// Some fields are highlighted as (required). All left fields may be missing in the data.

// - instead of "bio: true", vegan: true, and the values in "misc" we should print
//   a line with badges like (bio) (vegan) (NutriScore: A)
// - ignore the usedAmounts field

// The yml sample

$currency = '€';
$key = 'My food S Bio';
$data = [
  'productName' => '...',
  'vendor' => 'My vendor',
  'url' => '...',
  'acceptable' => 'less',
  'comment' => 'My comment',
  'bio' => true,
  'vegan' => false,
  'misc' => ['NutriScore' => 'A'],
  'ingredients' => '...',
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
  <title>Product Info</title>
</head>
<body>
  <div class="container mt-5">
    
    <p class="lead mb-1 fw-bold">
      <?= htmlspecialchars($key) ?> (<?= htmlspecialchars($data['vendor']) ?>)
    </p>
    
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
            <td><?= htmlspecialchars($data['ingredients']) ?></td>
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

    <p class="small">
      <?= str_replace(',', '<br>', htmlspecialchars($data['sources'])) ?>
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

    <p class="lead fw-bold">Nutritional values</p>

    <table class="table table-bordered">
      <tbody>

        <tr>
          <th>Calories</th>
          <td><?= $data['calories'] ?></td>
        </tr>

        <tr>
          <th colspan="2">Nutritional values</th>
        </tr>
        <?php foreach( $data['nutritionalValues'] as $key => $value): ?>
          <tr>
            <td><?= ucwords( str_replace('_', ' ', $key)) ?></td>
            <td><?= $value ?></td>
          </tr>
        <?php endforeach; ?>

        <?php if( ! empty($data['fattyAcids'])): ?>
          <tr>
            <th colspan="2">Fatty acids</th>
          </tr>
          <?php foreach( $data['fattyAcids'] as $key => $value): ?>
            <tr>
              <td><?= ucwords(str_replace('_', ' ', $key)) ?></td>
              <td><?= $value ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if( ! empty($data['aminoAcids'])): ?>
          <tr>
            <th colspan="2">Amino acids</th>
          </tr>
          <?php foreach( $data['aminoAcids'] as $key => $value): ?>
            <tr>
              <td><?= ucwords(str_replace('_', ' ', $key)) ?></td>
              <td><?= $value ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if( ! empty($data['vitamins'])): ?>
          <tr>
            <th colspan="2">Vitamins</th>
          </tr>
          <?php foreach( $data['vitamins'] as $key => $value): ?>
            <tr>
              <td><?= ucwords(str_replace('_', ' ', $key)) ?></td>
              <td><?= $value ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if( ! empty($data['minerals'])): ?>
          <tr>
            <th colspan="2">Minerals</th>
          </tr>
          <?php foreach( $data['minerals'] as $key => $value): ?>
            <tr>
              <td><?= ucwords(str_replace('_', ' ', $key)) ?></td>
              <td><?= $value ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if( ! empty($data['secondary'])): ?>
          <tr>
            <th colspan="2">Secondary Plant Substances</th>
          </tr>
          <?php foreach( $data['secondary'] as $key => $value): ?>
            <tr>
              <td><?= ucwords(str_replace('_', ' ', $key)) ?></td>
              <td><?= $value ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>

      </tbody>
    </table>
  </div>
  <script src="../../../lib/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
