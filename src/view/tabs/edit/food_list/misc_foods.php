<?php

extract($args);

?><div class="col-12 col-md-6 col-xxl-4 mt-2">  <!-- group col -->
                                
  <div class="row">            <!-- must be 2 here cause headline has inner padding -->
    <div class="col-12 px-2">  <!-- below outer container for the bg color (would be full width without) -->
      <div class = "p-1 px-2 fs-6 fw-bold d-flex justify-content-between align-items-center"
            style = "background-color: #e0e0e0;"
      >
        Misc foods
        <a data-bs-toggle="collapse" href="#miscCollapse" class="text-body-secondary" role="button">
          <i class="bi bi-arrow-down-circle"></i>
        </a>
      </div>
    </div>
  </div>

  <div id="miscCollapse" class="p-1 collapse show">
    <?php
        // array_merge( array_keys( $this->modelView->get('recipes'))  // TASK
    $all = array_keys( $this->modelView->get('foods'));

    foreach( $all as $foodName ):
  
      if( in_array( $foodName, $done))
        continue;

      $type = $this->modelView->has("recipes.$foodName") ? 'recipes' : 'foods';
      $amountData = $this->modelView->get("$type.$foodName");

      $accepColor = $this->model->get("foods.$foodName.acceptable") ?? 'n/a';
      $accepColor = ['less' => '#ffcccc', 'occasionally' => '#ffff88', 'n/a' => 'inherit'][$accepColor];

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
      <div class="food-item row" style="background-color: <?= $accepColor ?>;">
        <div class="col-6 p-1 px-2">
          <?= $foodName ?>
          <?= self::iif( $price && $cheap,    '<i class="bi bi-currency-exchange small text-secondary"></i>') ?>
          <?= self::iif( $price && $expensiv, self::switch( $this->settings->get('currency'), [
            'EUR' => '<i class="bi bi-currency-euro small text-secondary"></i>',
            'USD' => '<i class="bi bi-currency-dollar small text-secondary"></i>'
          ])) ?>
          <?= self::iif( $showInfo, '<i class="bi bi-info-circle small text-secondary"></i>') ?>
        </div>
        <!-- TASK: Simplify in controller ? default -->
        <?php foreach( $amountData as $amount => $data ): ?>
        <?php
        
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
