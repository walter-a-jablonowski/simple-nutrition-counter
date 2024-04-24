<!-- TASK: needs upd, layout.yml was changed -->

<div id="foodList" class="row">

  <!-- static #code/staticListEntries -->
  <!-- use diff entries in one line -->

  <div class="col">

    <div class="row">
      <div class="col-6" onclick = "...">
        Enter ...
      </div>
      <div class="col-6" onclick = "...">
        Expired food ...
      </div>
    </div>

    <!-- Coffee counter -->

    <div class="row">
      <div class="col">
        Coffee
      </div>
      <div class="col" onclick = "...">
        XXS
      </div>
      <div class="col" onclick = "...">
        XS
      </div>
      <div class="col" onclick = "...">
        M
      </div>
      <div class="col">
        [VALUE GREEN|RED]
      </div>
    </div>

  </div>

  <?php

  $done = [];

  foreach( $layout as $idx => $def ):

    $type      = is_array( $def )   ?  'group' : 'single';
    $groupName = $type == 'single'  ?  $def    : array_key_first($def);
    $entries   = $type == 'single'  ?  null    : array_values($def);

    $done[] = $name;  // left over will be printed below (done = foods and recipes n a single list)
  ?>

    <?php if( $type == 'single'): ?>
      <!-- TASK -->
    <?php elseif( $type == 'group'): ?>
      <div class="col">

        <?php if( $group ): ?>
          <div class="row">
            <div class="col-12 p-1 small">
              <?= $groupName ?>
            </div>
          </div>
        <?php endif; ?>

        <?php foreach( $entries as $name => $entry ): ?>

          <!-- TASK: we need name, amounts => nutirents in entry (merge on controller) -->

          <div class="row">
            <div class="col p-2">
              <?= $name ?>
            </div>
            <?php foreach( $entry as $amount => $data ):  // TASK ?>
              <div class="food-item col p-2" onclick="foodsCrl.foodItemClick(event)"
                data-food      = "<?= $amount ?>"
                data-calories  = "<?= $data['calories'] ?>"
                data-nutrients = "<?= htmlspecialchars( json_encode( $data['nutrients'])) ?>"
              >
                <?= $amount ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <!-- left over entries -->

  <!-- TASK -->

  <?php if( count($foods) > count($done)): ?>
    <div class="col">

      <?php foreach( $this->model->foods as $food => $entry): ?>

        <div class="row">
          <?php if( ! in_array( $food, $done)): ?>
            <div class="food-item col p-2" onclick="foodsCrl.foodItemClick(event)"
              data-food      = "<?= $food ?>"
              data-calories  = "<?= $entry['calories'] ?>"
              data-nutrients = "<?= htmlspecialchars( json_encode( $entry['nutrients'])) ?>"
            >
              <?= $food ?>
            </div>
          <?php endif; ?>
        </div>

      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
