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

  <div class="col-12 mt-1">  <!-- wrap in col = show above groups -->
    <div class="row">        <!-- break points same as in food groups below -->
      <div class   = "col-12 col-md-6 col-lg-4 col-xxl-3 p-1"
           onclick = "foodsCrl.newEntryBtn(event)"
      >
        Enter manually ...  <!-- TASK: also add month var and buyings here (maybe use some select that changes sub forms) -->
      </div>
      <!-- TASK: currently used save btn -->
      <div class   = "col-12 col-md-6 col-lg-4 col-xxl-3 p-1"
           onclick = "foodsCrl.saveDayEntriesBtnClick(event)"
      >
        Save ...
      </div>
<!--
      <div class   = "col-12 col-md-6 col-lg-4 col-xxl-3 p-1"
           onclick = "..."
      >
        Expired food ...
      </div>
-->
<!--
      < ?php if( $this->settings->get('layout.useCoffeeCounter')) ?>
        <div class   = "col-12 col-md-6 col-lg-4 col-xxl-3 p-1"
             onclick = "..."
        >
          Coffee
        </div>
      < ?php endif; ?>

      < ?php if( $this->settings->get('layout.useFillupsCounter')) ?>
        <div class   = "col-12 col-md-6 col-lg-4 col-xxl-3 p-1"
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

    if( $groupName == '(first_entries)' || ! ($def['list'] ?? []))  // no entry
      continue;
    
    $groupId = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $groupName));
  ?>
    <div class="col-12 col-md-6 col-lg-4 col-xxl-3 mt-2">  <!-- group col -->

      <div class="row">            <!-- px: we make the bs default padding smaller to save some space -->
        <div class="col-12 px-1">  <!-- below outer container for the bg color (would be full width without) -->
          <div class = "p-1 small fw-bold d-flex justify-content-between align-items-center"
               style = "background-color: <?= $def['@attribs']['color'] ?? '#e0e0e0' ?>;"
          >
            <div>
              <?= $groupName ?>
              <?php if( isset($def['@attribs']['(i)'])): ?>
                &nbsp;
                <button type="button" class="border-0 p-0 bg-transparent"
                        data-bs-toggle = "modal"
                        data-bs-target = "#infoModal"
                        data-source    = "#<?= $groupId ?>Data"
                >
                  <i class="bi bi-info-circle icon-circle"></i>
                </button>
              <?php endif; ?>
            </div>
            <a data-bs-toggle="collapse" href="#<?= $groupId ?>Collapse" class="text-body-secondary" role="button">
              <i class="bi bi-arrow-down-circle"></i>
            </a>
          </div>
        </div>
      </div>

      <?php if( isset($def['@attribs']['short'])): ?>
        <div class="row">                    <!-- px: we make the bs default padding smaller to save some space -->
          <div class = "col-12 px-2 small">  <!-- must be 2 here cause headline has inner padding -->
            <?= $def['@attribs']['short'] ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if( isset($def['@attribs']['(i)'])): ?>
        <div id="<?= $groupId ?>Data" class="d-none">
          <?= $def['@attribs']['(i)'] ?>
        </div>
      <?php endif; ?>

      <div id="<?= $groupId ?>Collapse" class="collapse show">

        <?php

        // if( is_null($foodNames))
        //   $debug = 'halt';
        
        foreach( $def['list'] as $idx => $foodName ):
        
          $type = $this->modelView->has("recipes.$foodName") ? 'recipes' : 'foods';
          $amountData = $this->modelView->get("$type.$foodName");

          $done[] = $foodName;  // left over will be printed below (done = foods and recipes in a single list)
        ?>
          <div class="row">
            <div class="col-6 px-2">  <!-- px: we make the bs default padding smaller to save some space -->
              <?= $foodName ?>        <!-- must be 2 here cause headline has inner padding -->
            </div>
            <!-- TASK: Simplify in controller ? default -->
            <?php
            
            // if( stripos( $foodName, 'Amino misc') !== false )  // DEBUG
            //   $debug = 'halt';
            
            ?>
            <?php foreach( $amountData as $amount => $data ): ?>  <!-- TASK: don't print more than 3 entries (maybe do in controller) -->
              <div class   = "food-item col-2"
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
    <div class="col-12 col-md-6 col-lg-4 col-xxl-3 mt-2">  <!-- group col -->
                                   <!-- px: we make the bs default padding smaller to save some space -->
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
          <div class="row">
            <div class="col-6 ps-1 pe-1">
              <?= $foodName ?>
            </div>
            <!-- TASK: Simplify in controller ? default -->
            <?php foreach( $amountData as $amount => $data ): ?>
            <?php
            
              if( ! isset($data['nutriVal']))
                $debug = 'halt';
            ?>
              <div class   = "food-item col-2"
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
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
