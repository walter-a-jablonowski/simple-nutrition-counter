<?php

// Can you make a PHP code that prints the yml sample as nice looking bootstrap 5.3 html?
// Some fields are highlighted as (required). All left fields may be missing in the data.

// - instead of "bio: true", vegan: true, and the values in "misc" we should print
//   a line with badges like (bio) (vegan) (NutriScore: A)
// - ignore the usedAmounts field

// The yml sample

$key = 'My food S Bio';
$data = [
  'productName' => '...',
  'vendor' => 'My vendor',
  'url' => '...',
  'acceptable' => 'less',
  'comment' => 'My comment',
  'bio' => true,
  'vegan' => true,
  'misc' => ['NutriScore' => 'A'],
  'ingredients' => '...',
  'origin' => '...',
  'cookingInstrutions' => 'First ...',
  'price' => 1.00,
  'weight' => '100g',
  'pieces' => 6,
  'calories' => [],
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
    'calcium' => null,
  ],
  'secondary' => [],
  'sources' => 'Macro nutrients: web|pack, information on packaging may differ slightly, nutrients: ..., price: ...',
  'lastUpd' => '2024-02-18',
  'lastPriceUpd' => '2024-03-23',
];

function generate_html($key, $data) {
  ob_start();
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../../lib/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Product Info</title>
  </head>
  <body>
    <div class="container mt-5">
      <h1><?= htmlspecialchars($key) ?></h1>
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Vendor</th>
            <td><?= htmlspecialchars($data['vendor']) ?></td>
          </tr>
          <tr>
            <th>Price</th>
            <td>$<?= htmlspecialchars($data['price']) ?></td>
          </tr>
          <tr>
            <th>Weight</th>
            <td><?= htmlspecialchars($data['weight']) ?></td>
          </tr>
          <tr>
            <th>Attributes</th>
            <td>
              <?php if (!empty($data['bio'])): ?><span class="badge bg-success">Bio</span> <?php endif; ?>
              <?php if (!empty($data['vegan'])): ?><span class="badge bg-success">Vegan</span> <?php endif; ?>
              <?php if (!empty($data['misc'])): ?>
                <?php foreach ($data['misc'] as $key => $value): ?>
                  <span class="badge bg-info"><?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?></span>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php if (!empty($data['acceptable'])): ?>
          <tr>
            <th>Acceptable</th>
            <td><?= htmlspecialchars($data['acceptable']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($data['comment'])): ?>
          <tr>
            <th>Comment</th>
            <td><?= htmlspecialchars($data['comment']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($data['ingredients'])): ?>
          <tr>
            <th>Ingredients</th>
            <td><?= htmlspecialchars($data['ingredients']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($data['origin'])): ?>
          <tr>
            <th>Origin</th>
            <td><?= htmlspecialchars($data['origin']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($data['cookingInstrutions'])): ?>
          <tr>
            <th>Cooking Instructions</th>
            <td><pre><?= htmlspecialchars($data['cookingInstrutions']) ?></pre></td>
          </tr>
          <?php endif; ?>
          <tr>
            <th>Sources</th>
            <td><?= htmlspecialchars($data['sources']) ?></td>
          </tr>
          <tr>
            <th>Last Update</th>
            <td><?= htmlspecialchars($data['lastUpd']) ?></td>
          </tr>
          <tr>
            <th>Last Price Update</th>
            <td><?= htmlspecialchars($data['lastPriceUpd']) ?></td>
          </tr>
        </tbody>
      </table>
      <h2>Nutritional Values</h2>
      <table class="table table-bordered">
        <tbody>
          <?php foreach ($data['nutritionalValues'] as $key => $value): ?>
            <?php if ($value !== null): ?>
            <tr>
              <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?></th>
              <td><?= htmlspecialchars($value) ?></td>
            </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <script src="../../../lib/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
  </html>
  <?php
  return ob_get_clean();
}

echo generate_html($key, $data);
?>
