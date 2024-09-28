<?php

extract($args);

$return['done'] = [];

?><div class="col-12 col-md-6 col-xxl-4 mt-2">  <!-- group col -->

  <div class="row">
    <div class="col-12">  <!-- below outer container for the bg color (would be full width without) -->
      <div id    = "<?= $groupId ?>Group"
           class = "p-1 px-2 fw-bold d-flex justify-content-between align-items-center"
           style = "background-color: <?= $def['@attribs']['color'] ?? '#e0e0e0' ?>;"
      >
        <div>
          <?= $groupName ?>
          <?php if( isset($def['@attribs']['(i)'])): ?>
            &nbsp;
            <button type="button" class="border-0 p-1 bg-transparent"
                    data-bs-toggle = "modal"
                    data-bs-target = "#infoModal"
                    data-title     = "<?= $groupName ?>"
                    data-source    = "#<?= $groupId ?>Data"
            >
              <i class="bi bi-info-circle icon-circle"></i>
            </button>

            <div id="<?= $groupId ?>Data" class="d-none">
              <?= $def['@attribs']['(i)'] ?>
            </div>
          <?php endif; ?>
        </div>
        <a data-bs-toggle="collapse" href="#<?= $groupId ?>Collapse" class="text-body-secondary" role="button">
          <i class="bi bi-arrow-down-circle"></i>
        </a>
      </div>
    </div>
  </div>

  <?php if( isset($def['@attribs']['short'])): ?>
    <div class="row mt-1">                    
      <div class = "col-12 px-2 small">
        &nbsp;<?= $def['@attribs']['short'] ?>  <!-- simple spacer -->
      </div>
    </div>
  <?php endif; ?>

  <div id="<?= $groupId ?>Collapse" class="p-1 collapse<?= self::iif( ! ($def['@attribs']['fold'] ?? false), ' show') ?>">

    <?php

    foreach( $def['list'] as $idx => $foodName ):

      // if( $foodName == 'Nussmisch N old' )  // DEBUG
      //   $debug = 'halt';

      if( ! ($args['showRemoved'] ?? false) && $this->foodsModel->get("$foodName.removed"))
        continue;  // no removed foods, even if in layout (see group Removed foods in UI)

      $foodId = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $foodName));  // TASK: use food id from SimpleData key as soon as upd, maybe we need prefix this so that no Ids get confused?
      $amountData = $this->foodsView->get($foodName);  // foods and recipes are merged in one

      $return['done'][] = $foodName;  // left over will be printed below (done = foods and recipes in a single list)

      $accepColor  = $this->foodsModel->get("$foodName.acceptable") ?? 'n/a';  // TASK: colors also below, merge by using a class (also in app tips)
      $accepColor  = ['less' => '#ffcccc', 'occasionally' => '#ffff88', 'n/a' => 'inherit'][$accepColor];

      $price       = $this->foodsModel->get("$foodName.price");
      $pricePer100 = $price / ( trim( $this->foodsModel->get("$foodName.weight"), "mgl ") / 100.0);

      if( $price )
      {
        $cheap     = $pricePer100 <  settings::get('cheap');
        $expensive = $pricePer100 >= settings::get('expensive');
      }

      $showInfo = $this->foodsModel->get("$foodName.comment")  // has comment might mean sth important
                ? true : false;
    ?>                             <!-- TASK: col-5 would be right, some problem with padding margin -->
      <div class="food-item row">  <!-- the margin below is the problem but needed for bg color (div for bg destroys layout) -->
        <div class = "col-4 p-1 ps-1" style="margin-left: 5px; background-color: <?= $accepColor ?>;"
             data-bs-toggle = "modal"
             data-bs-target = "#infoModal"
             data-title     = "#<?= $foodId ?>Headline"
             data-source    = "#<?= $foodId ?>Data"
        >
          <?= $foodName ?>
          <?= self::iif( $showInfo, '<i class="bi bi-info-circle-fill" style="color: orange;"></i>') ?>
          <?= self::iif( $price && $cheap, '<i class="bi bi-currency-exchange small text-secondary"></i>') ?>
          <!-- < ?= self::iif( $price && $expensive, settings::get('currencySymbol')) ?> -->
          <?= self::iif( $price && $expensive, '<i class="bi ' . settings::get('currencyIcon') . ' small text-secondary"></i>') ?>
          </div>
        <div id="<?= $foodId ?>Headline" class="d-none">
          <?php

            print $this->inc( __DIR__ . '/food_info/headline.php', [
              'foodId'   => $foodId,
              'foodName' => $foodName
            ]);
          ?>
        </div>
        <div id="<?= $foodId ?>Data" class="d-none">
          <?php

            print $this->inc( __DIR__ . '/food_info/content.php', [
              'foodId'      => $foodId,
              'foodName'    => $foodName,
              'pricePer100' => $pricePer100
            ]);
          ?>
        </div>
        <?php foreach( $amountData as $amount => $data ): ?>  <!-- TASK: don't print more than 3 entries (maybe do in controller) -->
        <?php
        
        // if( stripos( $foodName, 'Amino misc') !== false )  // DEBUG
        // if( ! isset($data['nutriVal']))
        //   $debug = 'halt';
        
        ?>
          <div class   = "col-2 p-1 text-center blink-yellow"
               onclick = "mainCrl.foodItemClick(event)"
               data-food       = "<?= $foodName ?>"
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
            <?= $amount ?>
          </div>
        <?php endforeach; ?>
        <!-- Spacer -->
        <?php for( $i=count($amountData)+1; $i < 4; $i++ ):  // plus one is the food menu ?>
          <div class="col-2" style="background-color: <?= $accepColor ?>;">&nbsp;</div>
        <?php endfor; ?>
        <!-- Food menu -->
        <div class   = "food-menu col-1 text-center"
             onclick = ""
             style   = "background-color: <?= $accepColor ?>;"
        >
          ...  <!-- TASK: Expired food, ... -->
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>
