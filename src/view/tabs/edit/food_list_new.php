<!-- TASK: needs upd, layout.yml was changed -->
<div id="foodList" class="row">

  <!-- static #code/staticListEntries -->
  <!-- use diff entries in one line -->

  <div class="col">

    <div class="row">
      <div class="col-6 p-1" onclick="foodsCrl.newEntryBtn(event)">
        Enter manually ...
      </div>
      <div class="col-6 p-1" onclick="...">
        Expired food ...  <!-- TASK: make kind of X checkbox instead ? like [ My food |1|2|3| X ] -->
      </div>
      <!-- TASK: currently used save btn -->
<!--
      <div class="col-6 p-1" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
        Save ...
      </div>
-->
    </div>
  </div>

  <?php

  $done = [];

  foreach( $this->layout as $groupName => $entries ):

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
      
      foreach( $entries as $name ):
      
        $type  = isset( $this->recipes[$name] ) ? 'recipe' : 'food';
        $entry = $this->modelView->get("$type.$name");

        $done[] = $name;  // left over will be printed below (done = foods and recipes in a single list)
      ?>

        <div class="row">
          <div class="col p-2">
            <?= $name ?>
          </div>
          <!-- TASK: Simplify in controller ? default -->
          <?php foreach( $entry['usedAmounts'] as $amount ): ?>
            <div class   = "food-item col p-1"
                 onclick = "foodsCrl.foodItemClick(event)"
                 data-food       = "<?= $name ?>"
                 data-calories   = "<?= $entry['calories'] ?>"
                 data-nutrients  = "<?= htmlspecialchars( json_encode( $data['nutriVal'])) ?>"
                 data-fattyacids = "<?= htmlspecialchars( dump_json( $entry['fat'])) ?>"
                 data-aminoacids = "<?= htmlspecialchars( dump_json( $entry['amino'])) ?>"
                 data-vitamins   = "<?= htmlspecialchars( dump_json( $entry['vit'])) ?>"
                 data-minerals   = "<?= htmlspecialchars( dump_json( $entry['min'])) ?>"
                 data-secondary  = "<?= htmlspecialchars( dump_json( $entry['sec'])) ?>"
                 data-price      = "<?= $entry['price'] ?>"
            >
              <?= $amount ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

    </div>
  <?php endforeach; ?>

  <!-- left over entries -->

  <?php if( count($foods) > count($done)): ?>
    <div class="col">

      <div class="row">
        <div class="col-12 p-1 small">
          Misc foods
        </div>
      </div>

      <?php
      
      foreach( $this->modelView->foods as $name => $entry):  // (TASK) recipes
    
        if( in_array( $name, $done))
          continue;
      ?>
        <div class="row">
          <div class="col p-2">
            <?= $name ?>
          </div>
          <?php foreach( $entry['usedAmounts'] as $amount ): ?>
            <div class    = "food-item col p-1"
                  onclick = "foodsCrl.foodItemClick(event)"
                  data-food       = "<?= $name ?>"
                  data-calories   = "<?= $entry['calories'] ?>"
                  data-nutrients  = "<?= htmlspecialchars( json_encode( $entry['nutriVal'])) ?>"
                  data-fattyacids = "<?= htmlspecialchars( dump_json( $entry['fat'])) ?>"
                  data-aminoacids = "<?= htmlspecialchars( dump_json( $entry['amino'])) ?>"
                  data-vitamins   = "<?= htmlspecialchars( dump_json( $entry['vit'])) ?>"
                  data-minerals   = "<?= htmlspecialchars( dump_json( $entry['min'])) ?>"
                  data-secondary  = "<?= htmlspecialchars( dump_json( $entry['sec'])) ?>"
                  data-price      = "<?= $entry['price'] ?>"
            >
              <?= $amount ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
