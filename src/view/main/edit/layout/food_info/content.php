<?php

extract($args);

$data = $this->combinedModel->get($entryName);

$comment = $data['comment'] ?? '';
$comment = true === $this->combinedModel->get("$entryName.xTimeLog")
         ? '<b><i class="bi bi-clock small text-secondary"></i> excluded from eating time calculation</b><br><br>' . $comment
         : $comment;

?>

<!-- Comment -->

<?php if( ! empty($comment)): ?>
  <div class="mb-3 p-2 small" style="background-color: #e0e0e0 !important;">
    <?= $comment ?>
  </div>
<?php endif; ?>

<!-- Table -->

<table class="table table-sm table-bordered m-0">
  <tbody>
    <?php if( ! empty($data['productName'])): ?>
      <tr>
        <th>Product name</th>
        <td><?= htmlspecialchars($data['productName']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['details'])): ?>
      <tr>
        <th>Details</th>
        <td><?= htmlspecialchars($data['details']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['ingredients'])): ?>
      <tr>
        <th>
          <div
            class           = "info-popover"
            data-bs-toggle  = "popover"
            data-bs-content = "<?= htmlspecialchars( $this->inlineHelp->get('foods.ingredients.usage')) ?>"
            data-bs-trigger = "click"
          >
            <!-- data-bs-container = "#infoModal" -->
            Ingredients
          </div>
        </th>
        <td>
          <a data-bs-toggle="collapse" href="#<?= $entryId ?>IngrCollapse" class="text-decoration-none" role="button">
            <span class="text-secondary small">show</span>
          </a>
        </td>
      </tr>
      <tr id="<?= $entryId ?>IngrCollapse" class="collapse">
        <td colspan="2" class="text-wrap" style="white-space: pre-wrap;"><?= $data['ingredients'] ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['allergy'])): ?>
      <tr>
        <th>Allergy</th>
        <td>
          <a data-bs-toggle="collapse" href="#<?= $entryId ?>AllCollapse" class="text-decoration-none" role="button">
            <span class="text-secondary small">show</span>
          </a>
        </td>
      </tr>
      <tr id="<?= $entryId ?>AllCollapse" class="collapse">
        <td colspan="2" class="text-wrap" style="white-space: pre-wrap;"><?= htmlspecialchars($data['allergy']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['mayContain'])): ?>
      <tr>
        <th>May contain</th>
        <td>
          <a data-bs-toggle="collapse" href="#<?= $entryId ?>MaybeCollapse" class="text-decoration-none" role="button">
            <span class="text-secondary small">show</span>
          </a>
        </td>
      </tr>
      <tr id="<?= $entryId ?>MaybeCollapse" class="collapse">
        <td colspan="2" class="text-wrap" style="white-space: pre-wrap;"><?= htmlspecialchars($data['mayContain']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['origin'])): ?>
      <tr>
        <th>Origin</th>
        <td><?= htmlspecialchars($data['origin']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['packaging'])): ?>
      <tr>
        <th>Packaging</th>
        <td><?= $data['packaging'] ?></td>
      </tr>
    <?php endif; ?>
    <?php if( ! empty($data['url']) && is_array($data['url'])): ?>
      <tr>
        <th>Urls</th>
        <td>
          <a data-bs-toggle="collapse" href="#<?= $entryId ?>UrlCollapse" class="text-decoration-none" role="button">
            <span class="text-secondary small">show</span>
          </a>
        </td>
      </tr>
      <tr id="<?= $entryId ?>UrlCollapse" class="collapse">
        <td colspan="2" class="text-nowrap" style="white-space: no-wrap;">
          <?php foreach( $data['url'] as $url ): ?>
            - <a href="<?= $url ?>" target="_blank" class="text-decoration-none"><?= $url ?></a><br>
          <?php endforeach; ?>
        </td>
      </tr>
    <?php endif; ?>
    <tr>
      <th>Price</th>
      <td class="price-col"<?= self::iif( ! empty($data['price']), ' onclick="mainCrl.priceColClick(event)"') ?>>
        <?php if( ! empty($data['price'])): ?>
          <span class="price-label-view">
            <?= settings::get('currencySymbol') ?>
            <!-- price (highlight expensive and cheap) -->
            <?php if( $pricePer100 >= settings::get('expensive')): ?>
              <span style="color: red;"><?= $data['price'] ?></span>
            <?php elseif( $pricePer100 <  settings::get('cheap')): ?>
              <span style="color: green;"><?= $data['price'] ?></span>
            <?php else: ?>
              <?= $data['price'] ?>
            <?php endif; ?>
            <?php if( $data['lastPriceUpd'] ): ?>
              <span class="text-secondary small">
                on <?= date('Y-m-d', $data['lastPriceUpd'] ) ?>
              </span>
            <?php endif; ?>
            <?php if( isset($data['dealPrice']) && $data['dealPrice'] ): ?>
              &nbsp;<span class="badge bg-warning text-dark">deal: <?= $data['dealPrice'] ?></span>
            <?php endif; ?>
          </span>
          <span class="price-input-view" style="display: none;">
            <!-- <input value="< ?= $data['price'] ?>" class="price-inp form-control form-control-sm d-inline-block" type="text" style="width: 80px; padding: 0;"> -->
            <div contenteditable="true" class="price-inp d-inline-block border px-1 py-0" style="min-width: 60px;">
              <?= $data['price'] ?>
            </div>  <!-- TASK: maybe move data-name or use id -->
            <button onclick="mainCrl.updPriceClick(event)" data-name="<?= $entryName ?>" class="upd-price btn btn-sm btn-secondary ml-1 px-1 py-0" style="margin-top: -4px;">update</button> 
          </span>
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
  <a data-bs-toggle="collapse" href="#<?= $entryId ?>SourcesCollapse" class="text-secondary text-decoration-none" style="font-size: .75em;" role="button">
    &nbsp;Data sources <i class="bi bi-caret-down"></i>
  </a>
  <span class="text-secondary" style="font-size: .75em;">
    Last update: <?= date('Y-m-d', $data['lastUpd'] ) ?>
  </span>
</div>
<table id="<?= $entryId ?>SourcesCollapse" class="table collapse" style="font-size: .75em;">
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
      'fattyAcids'        => 'Fatty acids',
      'carbs'             => 'Carbs',
      'aminoAcids'        => 'Amino acids',
      'vitamins'          => 'Vitamins',
      'minerals'          => 'Minerals',
      'secondary'         => 'Secondary plant substances',
      'misc'              => 'Misc',
      'water'             => 'Water'
    ];

    foreach( array_merge(['nutritionalValues'], static::NUTRIENT_GROUPS) as $groupName ):

      if( $groupName == 'nutritionalValues')
        $collapseId = $entryId . 'NutritionalValuesCollapse';
      else
        $collapseId = $entryId . ucwords( $this->nutrientsModel->get("$groupName.short")) . 'Collapse';
  ?>

    <?php if( ! empty($data[$groupName])): ?>

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >
        <?= $headlines[$groupName] ?>
        <a data-bs-toggle="collapse" href="#<?= $collapseId ?>" class="text-body-secondary" role="button">
          <i class="bi bi-arrow-down-circle"></i>
        </a>
      </li>

      <li id="<?= $collapseId ?>" class="list-group-item collapse">

        <table class="table table-bordered">
          <tbody>

            <?php foreach( $data[$groupName] as $key => $value): ?>
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
