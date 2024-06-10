<!-- TASK: needs upd, layout.yml was changed -->
<div id="foodList" class="row">

  <!-- static #code/staticListEntries -->
  <!-- use diff entries in one line -->

  <div class="col">

    <div class="row">
      <div class="col-6 p-1" onclick="foodsCrl.newEntryBtn(event)">
        Enter manually ...
      </div>
    </div>
    <div class="row">
      <div class="col-6 p-1" onclick="...">
        Expired food ...  <!-- TASK: make kind of X checkbox instead ? like [ My food |1|2|3| X ] -->
      </div>
    </div>
    <!-- TASK: currently used save btn -->
<!--
    <div class="row">
      <div class="col-6 p-1" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
        Save ...
      </div>
    </div>
-->
    <!-- TASK: Coffee ... -->
<!--
  < ?php foreach( $this->layout['(first_entries)'] as $foodName ): ?>

    <div class="row">
      <div class="col-6 p-1" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
        < ?= $foodName ?>
      </div>
    </div>
  < ?php endforeach; ?>
-->
  </div>

  <?php

  $done = [];

  foreach( $this->layout as $groupName => $foodNames ):

    if( $groupName == '(first_entries)') // TASK
      continue;
  ?>
    <div class="col">

      <div class="row">
        <div class="col-12 p-1 small">
          <?= $groupName ?>
        </div>
      </div>

      <?php
      
      foreach( $foodNames as $foodName ):
      
        $type = $this->modelView->has("recipes.$foodName") ? 'recipes' : 'foods';
        $amountData = $this->modelView->get("$type.$foodName");  // for debugging we need modify the key in controller (has amount in front)

        $done[] = $foodName;  // left over will be printed below (done = foods and recipes in a single list)
      ?>
        <div class="row">
          <div class="col p-2">
            <?= $foodName ?>
          </div>
          <!-- TASK: Simplify in controller ? default -->
          <?php foreach( $amountData as $amount => $data ): ?>
            <div class   = "food-item col p-1"
                 onclick = "foodsCrl.foodItemClick(event)"
                 data-food       = "<?= $foodName ?>"
                 data-calories   = "<?= $data['calories'] ?>"
                 data-nutrients  = "<?= htmlspecialchars( json_encode( $data['nutriVal'])) ?>"
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
        </div>
      <?php endforeach; ?>

    </div>
  <?php endforeach; ?>

  <!-- left over entries -->

  <?php
  
  $all = count( $this->modelView->get('foods'));
    // + count( $this->modelView->get('recipes'));  // TASK
  
  if( $all > count($done)):
  
  ?>
    <div class="col">

      <div class="row">
        <div class="col-12 p-1 small">
          Misc foods
        </div>
      </div>

      <?php
          // array_merge( array_keys( $this->modelView->get('recipes'))  // TASK
      $all = array_keys( $this->modelView->get('foods'));

      foreach( $all as $foodName ):
    
        if( in_array( $foodName, $done))
          continue;

        $type = $this->modelView->has("recipes.$foodName") ? 'recipes' : 'foods';
        $amountData = $this->modelView->get("$type.$foodName");  // for debugging we need modify the key in controller (has amount in front)
      ?>
        <div class="row">
          <div class="col p-2">
            <?= $foodName ?>
          </div>
          <!-- TASK: Simplify in controller ? default -->
          <?php foreach( $amountData as $amount => $data ): ?>
            <div class   = "food-item col p-1"
                 onclick = "foodsCrl.foodItemClick(event)"
                 data-food       = "<?= $foodName ?>"
                 data-calories   = "<?= $data['calories'] ?>"
                 data-nutrients  = "<?= htmlspecialchars( json_encode( $data['nutriVal'])) ?>"
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
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
