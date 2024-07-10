<?php

extract($args);

$data = $this->model->get("foods.$foodName");

$nutrientsShort = [
  'nutritionalValues' => 'nutriVal',  // TASK: use from controller
  'fattyAcids'        => 'fat',
  'aminoAcids'        => 'amino',
  'vitamins'          => 'vit',
  'minerals'          => 'min',
  'secondary'         => 'sec'
];

?>

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
    <?php if( ! empty($data['allergy'])): ?>
      <tr>
        <th>Allergy</th>
        <td>
          <a data-bs-toggle="collapse" href="#<?= $foodId ?>AllCollapse" class="text-decoration-none" role="button">
            <span class="text-secondary small">show</span>
          </a>
        </td>
      </tr>
      <tr id="<?= $foodId ?>AllCollapse" class="collapse">
        <td colspan="2" class="text-wrap" style="white-space: pre-wrap;"><?= htmlspecialchars($data['allergy']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['mayContain'])): ?>
      <tr>
        <th>May contain</th>
        <td>
          <a data-bs-toggle="collapse" href="#<?= $foodId ?>MaybeCollapse" class="text-decoration-none" role="button">
            <span class="text-secondary small">show</span>
          </a>
        </td>
      </tr>
      <tr id="<?= $foodId ?>MaybeCollapse" class="collapse">
        <td colspan="2" class="text-wrap" style="white-space: pre-wrap;"><?= htmlspecialchars($data['mayContain']) ?></td>
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
    <?php if( ! empty($data['url']) && is_array($data['url'])): ?>
      <tr>
        <th>Urls</th>
        <td>
          <a data-bs-toggle="collapse" href="#<?= $foodId ?>UrlCollapse" class="text-decoration-none" role="button">
            <span class="text-secondary small">show</span>
          </a>
        </td>
      </tr>
      <tr id="<?= $foodId ?>UrlCollapse" class="collapse">
        <td colspan="2" class="text-nowrap" style="white-space: no-wrap;">
          <?php foreach( $data['url'] as $url ): ?>
            - <a href="<?= $url ?>" target="_blank" class="text-decoration-none"><?= $url ?></a><br>
          <?php endforeach; ?>
        </td>
      </tr>
    <?php endif; ?>
    <tr>
      <th>Price</th>
      <td>
        <?php if( ! empty($data['price'])): ?>
          <?= $this->settings->get('currencySymbol') ?>
          <!-- price (highlight expensiv and cheap) -->
          <?php if( $pricePer100 >= $this->settings->get('expensiv')): ?>
            <span style="color: red;"><?= $data['price'] ?></span>
          <?php elseif( $pricePer100 <  $this->settings->get('cheap')): ?>
            <span style="color: green;"><?= $data['price'] ?></span>
          <?php else: ?>
            <?= $data['price'] ?>
          <?php endif; ?>
          <?php if( $data['lastPriceUpd'] ): ?>
            <span class="text-secondary small">
              on <?= date('Y-m-d', $data['lastPriceUpd'] ) ?>
            </span>
          <?php endif; ?>
        <?php endif; ?>
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
  <span class="text-secondary" style="font-size: .75em;">
    Last update: <?= date('Y-m-d', $data['lastUpd'] ) ?> (<?= $data['state'] ?>)
  </span>
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

<!-- Cooking instructions -->

<?php if( ! empty($data['cookingInstructions'])): ?>
  <div class="mt-3 p-2 small" style="background-color: #D3B79C !important;">
    <b>Cooking instructions</b><br>
    <br>
    <?= $data['cookingInstructions'] ?>
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
