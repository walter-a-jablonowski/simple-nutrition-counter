<!--

Structure overview

- row.foodList
  - col-12   Static entries   cols break on small devices
  - cols     Food groups      (see "group col")
    - row                     per food entry
      - col                   per amount
  - cols     Left over foods  same code as food groups
    - ...

--><div id="foodList" class="row">

  <span id="uiMsg"></span>  <!-- TASK: mov -->

  <!-- Static entries #code/staticListEntries -->
  <!-- TASK: maybe use: smartphone 1 col, tabl 2 col, large 3 or col -->
  <!-- 3 col: col-12 col-md-6 col-lg-4 col-xxl-3 -->
  <!-- 2 col: col-12 col-md-6 col-xxl-4 -->

  <div class="col-12 mt-1">  <!-- wrap in col = show above groups -->
    <div class="row">        <!-- break points same as in food groups below -->
      <div class   = "col-12 col-md-6 col-xxl-4 p-2 py-1"
           onclick = "foodsCrl.newEntryBtn(event)"
      >
        Enter manually ...  <!-- TASK: also buyings here (maybe use some select that changes sub forms) -->
      </div>                <!-- month var: we have a layout group for this -->
      <!-- TASK: currently used save btn -->
      <div class   = "col-12 col-md-6 col-xxl-4 p-2 py-1"
           onclick = "foodsCrl.saveDayEntriesBtnClick(event)"
      >
        Save ...
      </div>
<!-- TASK: maybe leave above as a shortcut? see also (first_entries) -->
<!--
      <div class   = "col-12 col-md-6 col-xxl-4 p-2 py-1"
           onclick = "..."
      >
        Coffee
      </div>

      < ?php if( $this->config->get('special')) ?>
        <div class   = "col-12 col-md-6 col-xxl-4 p-2 py-1"
             onclick = "..."
        >
          Fillup
        </div>
      < ?php endif; ?>
-->
    </div>
  </div>

  <!-- Food groups -->

  <?php

  $done = [];

  foreach( $this->layout as $groupName => $def ):

    if( $groupName == '(first_entries)' || ! ($def['list'] ?? []))  // no entry  // TASK: first_entries currently no use
      continue;
    
    $groupId = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $groupName));
  ?>
    <div class="col-12 col-md-6 col-xxl-4 mt-2">  <!-- group col -->

      <div class="row">
        <div class="col-12 px-2">  <!-- below outer container for the bg color (would be full width without) -->
          <div class = "p-1 small fw-bold d-flex justify-content-between align-items-center"
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
          <div class = "col-12 px-2 small">  <!-- must be 2 here cause headline has inner padding -->
            <?= $def['@attribs']['short'] ?>
          </div>
        </div>
      <?php endif; ?>

      <div id="<?= $groupId ?>Collapse" class="collapse<?= self::iif( ! ($def['@attribs']['fold'] ?? false), ' show') ?>">

        <?php

        // if( is_null($foodNames))
        //   $debug = 'halt';
        
        foreach( $def['list'] as $idx => $foodName ):
        
          $type = $this->modelView->has("recipes.$foodName") ? 'recipes' : 'foods';
          $amountData = $this->modelView->get("$type.$foodName");  // TASK: rename

          $done[] = $foodName;  // left over will be printed below (done = foods and recipes in a single list)
          // TASK: maybe we need prefix this so that no Ids get confused?
          $foodId = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $foodName));  // TASK: use food id from SimpleData key as soon as upd
        ?>                             
          <div class="food-item row">            <!-- must be 2 here cause headline has inner padding -->
            <div class = "col-6 p-1 px-2"
                 data-bs-toggle = "modal"
                 data-bs-target = "#infoModal"
                 data-title     = "#<?= $foodId ?>Headline"
                 data-source    = "#<?= $foodId ?>Data"
            >
              <?= $foodName ?>
            </div>
            <div id="<?= $foodId ?>Headline" class="d-none">
              <?php

                print $this->inc( __DIR__ . '/food_info_headline.php', [
                  'foodId'   => $foodId,
                  'foodName' => $foodName
                ]);
              ?>
            </div>
            <div id="<?= $foodId ?>Data" class="d-none">
              <?php

                print $this->inc( __DIR__ . '/food_info.php', [
                  'foodId'   => $foodId,
                  'foodName' => $foodName
                ]);
              ?>
            </div>
            <!-- TASK: Simplify in controller ? default -->
            <?php
            
            // if( stripos( $foodName, 'Amino misc') !== false )  // DEBUG
            //   $debug = 'halt';
            
            ?>
            <?php foreach( $amountData as $amount => $data ): ?>  <!-- TASK: don't print more than 3 entries (maybe do in controller) -->
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
  <?php endforeach; ?>

  <!-- Left over foods -->

  <?php
  
  $all = count( $this->modelView->get('foods'));
    // + count( $this->modelView->get('recipes'));  // TASK
  
  if( $all > count($done)):
  
  ?>
    <div class="col-12 col-md-6 col-xxl-4 mt-2">  <!-- group col -->
                                   
      <div class="row">            <!-- must be 2 here cause headline has inner padding -->
        <div class="col-12 px-2">  <!-- below outer container for the bg color (would be full width without) -->
          <div class = "p-1 small fw-bold d-flex justify-content-between align-items-center"
               style = "background-color: #e0e0e0;"
          >
            Misc foods
            <a data-bs-toggle="collapse" href="#miscCollapse" class="text-body-secondary" role="button">
              <i class="bi bi-arrow-down-circle"></i>
            </a>
          </div>
        </div>
      </div>

      <div id="<?= $groupName ?>Collapse" class="collapse show">
        <?php
            // array_merge( array_keys( $this->modelView->get('recipes'))  // TASK
        $all = array_keys( $this->modelView->get('foods'));

        foreach( $all as $foodName ):
      
          if( in_array( $foodName, $done))
            continue;

          $type = $this->modelView->has("recipes.$foodName") ? 'recipes' : 'foods';
          $amountData = $this->modelView->get("$type.$foodName");
        ?>
          <div class="food-item row">
            <div class="col-6 p-1 px-2">
              <?= $foodName ?>
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
  <?php endif; ?>
</div>
