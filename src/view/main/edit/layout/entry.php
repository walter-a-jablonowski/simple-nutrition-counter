<?php

extract($args);

$return['done'] = [];

// if( $entryName == 'Nussmisch N old' )  // DEBUG
//   $debug = 'halt';

$entryId    = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $entryName));  // TASK: use id from SimpleData key as soon as upd, maybe we need prefix this so that no Ids get confused?
$amountData = $this->layoutView->get($entryName);  // foods and recipes are merged in one

$return['done'][] = $entryName;  // left over will be printed below (done = foods and recipes in a single list)

$accepColor  = $this->foodsModel->get("$entryName.acceptable") ?? 'n/a';  // TASK: colors also below, merge by using a class (also in app tips)
$accepColor  = ['less' => '#ffcccc', 'occasionally' => '#ffff88', 'n/a' => 'inherit'][$accepColor];

$price       = $this->foodsModel->get("$entryName.price");
$pricePer100 = $price / ( trim( $this->foodsModel->get("$entryName.weight"), "mgl ") / 100.0);

if( $price )
{
  $cheap     = $pricePer100 <  settings::get('cheap');
  $expensive = $pricePer100 >= settings::get('expensive');
}

$showInfo = $this->foodsModel->get("$entryName.comment")  // has comment might mean sth important
          ? true : false;
?>
<div class="layout-item row">  <!-- pe-0 is for bg color, TASK: alternative: highlight name only -->
  <div class = "col-5 ps-1 pe-0"
       data-bs-toggle = "modal"
       data-bs-target = "#infoModal"
       data-title     = "#<?= $entryId ?>Headline"
       data-source    = "#<?= $entryId ?>Data"
  >
    <div class="text-nowrap ms-1 ps-1 py-1 overflow-hidden" style="background-color: <?= $accepColor ?>;">
      <?= $entryName ?>
      <?= self::iif( $showInfo, '<i class="bi bi-info-circle-fill" style="color: orange;"></i>') ?>
      <?= self::iif( $price && $cheap, '<i class="bi bi-currency-exchange small text-secondary"></i>') ?>
      <!-- < ?= self::iif( $price && $expensive, settings::get('currencySymbol')) ?> -->
      <?= self::iif( $price && $expensive, '<i class="bi ' . settings::get('currencyIcon') . ' small text-secondary"></i>') ?>
    </div>
  </div>
  <div id="<?= $entryId ?>Headline" class="d-none">
    <?php

      print $this->renderView( __DIR__ . '/food_info/headline.php', [
        'entryId'   => $entryId,
        'entryName' => $entryName
      ]);
    ?>
  </div>
  <div id="<?= $entryId ?>Data" class="d-none">
    <?php

      print $this->renderView( __DIR__ . '/food_info/content.php', [
        'entryId'     => $entryId,
        'entryName'   => $entryName,
        'pricePer100' => $pricePer100
      ]);
    ?>
  </div>
  <?php foreach( $amountData as $amount => $data ): ?>  <!-- TASK: don't print more than 3 entries (rest in menu) -->
  <?php
  
  // if( stripos( $entryName, 'Amino misc') !== false )  // DEBUG
  // if( ! isset($data['nutriVal']))
  //   $debug = 'halt';
  
  ?>                     <!-- pe-0 is for bg color -->
    <div class   = "amount-btn col-2 p-1 pe-0 text-center"
         onclick = "mainCrl.layoutItemClick(event)"
         data-food       = "<?= $entryName ?>"
         data-calories   = "<?= $data['calories'] ?>"
         data-nutritionalvalues = "<?= htmlspecialchars( json_encode( $data['nutriVal'])) ?>"
         data-fattyacids = "<?= htmlspecialchars( dump_json( $data['fat'])) ?>"
         data-aminoacids = "<?= htmlspecialchars( dump_json( $data['amino'])) ?>"
         data-vitamins   = "<?= htmlspecialchars( dump_json( $data['vit'])) ?>"
         data-minerals   = "<?= htmlspecialchars( dump_json( $data['min'])) ?>"
         data-secondary  = "<?= htmlspecialchars( dump_json( $data['sec'])) ?>"
         data-price      = "<?= $data['price'] ?>"
         style           = "background-color: <?= $accepColor ?>;"
    >
      <div style="background-color: <?= $accepColor ?>;">
        <div class="blink-yellow" style="cursor: pointer;">  <!-- TASK: currently needed for blink to work, seems conflicts with bg color -->
          <?= str_replace('[FL]', '.', $amount) ?>  <!-- enable floating point number as key -->
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <!-- Spacer -->
  <?php for( $i = count($amountData) + 1; $i < 4; $i++ ):  // plus one is the menu ?>
    <div class="col-2" style="background-color: <?= $accepColor ?>;">&nbsp;</div>
  <?php endfor; ?>
  <!-- Entry menu -->
  <div class   = "layout-item-menu col-1 ps-0 pe-2 text-center"
       onclick = ""
  >
    <div class="py-1" style="background-color: <?= $accepColor ?>;">
      ...  <!-- TASK: menu of single entry: overflow amounts ... expired -->
    </div>
  </div>
</div>
