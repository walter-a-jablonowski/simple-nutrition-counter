<?php

extract($args);

$return['done'] = [];

?><div class="col-12 col-md-6 col-xxl-4 mt-2">  <!-- group col -->

  <div class="row">            <!-- must be 2 here cause headline has inner padding -->
    <div class="col-12 px-2">  <!-- below outer container for the bg color (would be full width without) -->
      <div class = "p-1 px-2 fw-bold d-flex justify-content-between align-items-center"
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
      <div class = "col-12 px-2 small">         <!-- must be 2 here cause headline has inner padding -->
        &nbsp;<?= $def['@attribs']['short'] ?>  <!-- simple spacer -->
      </div>
    </div>
  <?php endif; ?>

  <div id="<?= $groupId ?>Collapse" class="p-1 collapse<?= self::iif( ! ($def['@attribs']['fold'] ?? false), ' show') ?>">

    <?php

    foreach( $def['list'] as $idx => $foodName ):

      // if( $foodName == 'Hanuta' )  // DEBUG
      //   $debug = 'halt';

      $foodId = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $foodName));  // TASK: use food id from SimpleData key as soon as upd, maybe we need prefix this so that no Ids get confused?
      $type   = $this->foodsView->has("recipes.$foodName") ? 'recipes' : 'foods';
      $amountData = $this->foodsView->get("$type.$foodName");  // TASK: rename

      $return['done'][] = $foodName;  // left over will be printed below (done = foods and recipes in a single list)

      $accepColor  = $this->model->get("foods.$foodName.acceptable") ?? 'n/a';  // TASK: colors also below, merge by using a class (also in app tips)
      $accepColor  = ['less' => '#ffcccc', 'occasionally' => '#ffff88', 'n/a' => 'inherit'][$accepColor];

      $price       = $this->model->get("foods.$foodName.price");
      $pricePer100 = $price / ( trim( $this->model->get("foods.$foodName.weight"), "mgl ") / 100.0);

      if( $price )
      {
        $cheap     = $pricePer100 <  settings::get('cheap');
        $expensive = $pricePer100 >= settings::get('expensive');
      }

      // use for all the rest that has no icon

      $showInfo = $this->model->get("foods.$foodName.comment")  // has comment might mean sth important
                ? true : false;
    ?>                             
      <div class="food-item row" style="background-color: <?= $accepColor ?>;">  <!-- must be 2 here cause headline has inner padding -->
        <div class = "col-6 p-1 px-2"
              data-bs-toggle = "modal"
              data-bs-target = "#infoModal"
              data-title     = "#<?= $foodId ?>Headline"
              data-source    = "#<?= $foodId ?>Data"
        >
          <?= $foodName ?>
          <!-- < ?= self::iif( $showInfo, '<span class="badge" style="background-color: orange; padding: 2px 5px 4px 5px !important;">info</span>') ?> -->
          <?= self::iif( $showInfo,
            // bi bi-info-circle-fill
            '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="orange" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
              <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
            </svg>'
          ) ?>
          <?= self::iif( $price && $cheap, '<i class="bi bi-currency-exchange small text-secondary"></i>') ?>
          <!-- TASK: use &euro; or the icon in config -->
          <?= self::iif( $price && $expensive, self::switch( settings::get('currency'), [
            'EUR' => '<i class="bi bi-currency-euro small text-secondary"></i>',
            'USD' => '<i class="bi bi-currency-dollar small text-secondary"></i>'
          ])) ?>
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
        <!-- TASK: Simplify in controller ? default -->
        <?php foreach( $amountData as $amount => $data ): ?>  <!-- TASK: don't print more than 3 entries (maybe do in controller) -->
        <?php
        
        // if( stripos( $foodName, 'Amino misc') !== false )  // DEBUG
        // if( ! isset($data['nutriVal']))
        //   $debug = 'halt';
        
        ?>
          <div class   = "col-1 p-1 blink-yellow"
               onclick = "foodsCrl.foodItemClick(event)"
               data-food       = "<?= $foodName ?>"
               data-calories   = "<?= $data['calories'] ?>"
               data-nutritionalvalues = "<?= htmlspecialchars( json_encode( $data['nutriVal'])) ?>"
               data-fattyacids = "<?= htmlspecialchars( dump_json( $data['fat'])) ?>"
               data-aminoacids = "<?= htmlspecialchars( dump_json( $data['amino'])) ?>"
               data-vitamins   = "<?= htmlspecialchars( dump_json( $data['vit'])) ?>"
               data-minerals   = "<?= htmlspecialchars( dump_json( $data['min'])) ?>"
               data-secondary  = "<?= htmlspecialchars( dump_json( $data['sec'])) ?>"
               data-price      = "<?= $data['price'] ?>"
          >
            <?= $amount ?>
          </div>
        <?php endforeach; ?>
        <!-- Spacer -->
        <?php for( $i=count($amountData)+1; $i < 4; $i++ ):  // plus one is the food menu ?>
          <div class="col-1">&nbsp;</div>
        <?php endfor; ?>
        <!-- Food menu -->
        <div class   = "food-menu col-1"
              onclick = ""
        >
          ...  <!-- TASK: Expired food, ... -->
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>
