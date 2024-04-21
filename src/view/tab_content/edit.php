<div class="row">
  <div class="col">

    <!-- Day entries -->

    <textarea id="dayEntries" class="form-control" wrap="off" style="font-family: monospace;" rows="5"
    ><?= $this->model->dayEntriesTxt ?></textarea>

    <!-- full ui (#code/advancedDayEntries) -->
<!--
    <div class="scrollable-list">
      <ul id="dayEntries" class="list-group">
        < ?php for( $i=0; $i < 4; $i++): ?>
        <!-- < ?php foreach( $this->model->... ): ?> -- >
          <li class   = "food-item p-0 list-group-item d-flex justify-content-between align-items-center"
              onclick = ""
              data-type      = "food"
              data-weight    = ""
              data-calories  = ""    v make own function
              data-nutrients = "< ?= html_encode( json_encode( $entry['nutrients'])) ?>"
          >
            <span>
              <span class="handle bi bi-grip-vertical"></span>
              < ?= "UI demo food $i" ?>
            </span>
            <div>
              <i class="bi bi-pencil-square btn px-0"></i>
              <button type="button" class="btn">  <!-- maybe add the (-) or make btn in dlg -- >
                <i class="bi bi-dash-circle"></i>
              </button>
            </div>
          </li>
        < ?php endfor; ?>
      </ul>
    </div>
-->

    <!-- Quick summary -->
    
    <div class="row mt-2">  <!-- text-start text-left same for diff bs versions -->
      <div class="col ml-2 pl-1 pr-1 bg-secondary text-start text-left small">
        <b>H2O</b>
      </div>
      <div class="col pl-1 pr-1 bg-secondary text-start text-left small">
        kcal
      </div>
      <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
        <div class="align-split small"><span>Fat</span> <span>g</span></div>  <!-- align-split ai made class -->
      </div>
      <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
        <div class="align-split small"><span>Amino</span> <span>g</span></div>
      </div>
      <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
        <div class="align-split small"><span>Salt</span> <span>g</span></div>
      </div>
      <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left small">
        <b>Price</b>
      </div>
      <div class="col mr-2 pl-1 text-end text-right" style="max-height: 20px;">

        <button class="btn btn-sm btn-light" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
          Save
        </button>

      </div>
    </div>
    <div class="row">
      <div id="h2oSum"      class="col ml-2 pl-1 pr-1 text-start text-left">0</div>
      <div id="caloriesSum" class="col pl-1 pr-1 text-start text-left">0</div>
      <div id="fatSum"      class="col pl-1 pr-1 text-end text-right">0</div>
      <div id="aminoSum"    class="col pl-1 pr-1 text-end text-right">0</div>
      <div id="saltSum"     class="col pl-1 pr-1 text-end text-right">0</div>
      <div id="priceSum"    class="col pl-1 pr-1 text-end text-right">0</div>
      <div class="col mr-2 pl-1 text-end text-right">
        &nbsp;
      </div>
    </div>

  </div>
</div>

<!-- Food list -->

<div class="row mt-2">
  <div class="col scrollable-list">

    <ul id="foodList" class="list-group">
      <?php foreach( $this->model->foods as $food => $entry): ?>
        <li class   = "food-item p-1 list-group-item d-flex justify-content-between align-items-center"
            style   = "font-size: 1.5rem;"
            onclick = "foodsCrl.foodItemClick(event)"
            data-food      = "<?= $food ?>"
            data-calories  = "<?= $entry['calories'] ?>"
            data-nutrients = "<?= htmlspecialchars( json_encode( $entry['nutrients'])) ?>"
        >
          <?= $food ?>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- TASK: needs upd, layout.yml was changed -->
<!--
    <div id="foodList" class="row">

      <!-- static #code/staticListEntries -- >
      <!-- use diff entries in one line -- >

      <div class="col">

        <div class="row">
          <div class="col-12" onclick = "...">
            Expired food ...
          </div>
        </div>

      </div>

      < ?php
      
      $done = [];
      
      foreach( $layout as $idx => $def ):

        $type      = is_array( $def )   ?  'group' : 'single';
        $groupName = $type == 'single'  ?  $def    : array_key_first($def);
        $entries   = $type == 'single'  ?  null    : array_values($def);

        $done[] = $name;  // left over will be printed below (done = foods and recipes n a single list)
      ?>
        
        < ?php if( $type == 'single'): ?>
          // TASK
        < ?php elseif( $type == 'group'): ?>
          <div class="col">

            < ?php if( $group ): ?>
              <div class="row">
                <div class="col-12 p-1 small">
                  < ?= $groupName ?>
                </div>
              </div>
            < ?php endif; ?>

            < ?php foreach( $entries as $name => $entry ): ?>

              // TASK: we need name, amounts => nutirents in entry (merge on controller)

              <div class="row">
                <div class="col p-2">
                  < ?= $name ?>
                </div>
                < ?php foreach( $entry as $amount => $data ):  // TASK ?>
                  <div class="food-item col p-2" onclick="foodsCrl.foodItemClick(event)"
                    data-food      = "< ?= $amount ?>"
                    data-calories  = "< ?= $data['calories'] ?>"
                    data-nutrients = "< ?= htmlspecialchars( json_encode( $data['nutrients'])) ?>"
                  >
                    < ?= $amount ?>
                  </div>
                < ?php endforeach; ?>
              </div>
            < ?php endforeach; ?>

          </div>
        < ?php endif; ?>
      < ?php endforeach; ?>

      <!-- left over entries -- >

      // TASK

      < ?php if( count($foods) > count($done)): ?>
        <div class="col">

          < ?php foreach( $this->model->foods as $food => $entry): ?>

            <div class="row">
              < ?php if( ! in_array( $food, $done)): ?>
                <div class="food-item col p-2" onclick="foodsCrl.foodItemClick(event)"
                  data-food      = "< ?= $food ?>"
                  data-calories  = "< ?= $entry['calories'] ?>"
                  data-nutrients = "< ?= htmlspecialchars( json_encode( $entry['nutrients'])) ?>"
                >
                  < ?= $food ?>
                </div>
              < ?php endif; ?>
            </div>

          < ?php endforeach; ?>
        </div>
      < ?php endif; ?>

    </div>
-->
  </div>
</div>
