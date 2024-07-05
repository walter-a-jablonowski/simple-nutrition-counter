<?php

extract($args);

$return['done'] = [];

// TASK: (advanced) merge with misc_foods if possible
//       alterenative: food-item in sep fil and leave the food groups (cause diff code)
//       food info is missing in misc foods, fill up first

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
      $type   = $this->modelView->has("recipes.$foodName") ? 'recipes' : 'foods';
      $amountData = $this->modelView->get("$type.$foodName");  // TASK: rename

      $return['done'][] = $foodName;  // left over will be printed below (done = foods and recipes in a single list)

      $accepColor  = $this->model->get("foods.$foodName.acceptable") ?? 'n/a';  // TASK: colors also below, merge by using a class (also in app tips)
      $accepColor  = ['less' => '#ffcccc', 'occasionally' => '#ffff88', 'n/a' => 'inherit'][$accepColor];

      $price       = $this->model->get("foods.$foodName.price");
      $pricePer100 = $price / ( trim( $this->model->get("foods.$foodName.weight"), "mgl ") / 100.0);

      if( $price )
      {
        $cheap    = $pricePer100 <  $this->settings->get('cheap');
        $expensiv = $pricePer100 >= $this->settings->get('expensiv');
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
          <?= self::iif( $price && $cheap,    '<i class="bi bi-currency-exchange small text-secondary"></i>') ?>
          <?= self::iif( $price && $expensiv, self::switch( $this->settings->get('currency'), [
            'EUR' => '<i class="bi bi-currency-euro small text-secondary"></i>',
            'USD' => '<i class="bi bi-currency-dollar small text-secondary"></i>'
          ])) ?>
          <?= self::iif( $showInfo, '<i class="bi bi-info-circle small text-secondary"></i>') ?>
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
