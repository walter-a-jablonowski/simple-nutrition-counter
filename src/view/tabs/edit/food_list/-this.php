<div id="foodList" class="row">

  <span id="uiMsg"></span>  <!-- TASK: mov somewhere outside tab or one msg per tab (or use a toast) -->

  <!-- TASK: maybe use: smartphone 1 col, tabl 2 col, large 3 or col -->
  <!-- 3 col: col-12 col-md-6 col-lg-4 col-xxl-3 -->
  <!-- 2 col: col-12 col-md-6 col-xxl-4 -->

  <!-- TASK: add a collapse expand all (single btn) -->

  <!-- TASK: leave single group above full width for coffee, water, ... which is just normal food entries, see also (first_entries)
       put the btn somewhere on the side or own line?

       Water | Coffee | ... [new ...]  <-- highlight btn bg

       Water use food: Wasser lg, Wasser sm
  -->

  <div class="col-12 mt-1">  <!-- wrap in col = show above groups -->
    <div class="row">        <!-- break points same as in food groups below -->
      <div class   = "col-12 col-md-6 col-xxl-4 p-2 py-1"
           onclick = "mainCrl.newEntryBtn(event)"
      >
        Enter manually ...  <!-- TASK: also buyings here (maybe use some select that changes sub forms) -->
      </div>                <!-- month var: we have a layout group for this -->
      <!-- TASK: currently used save btn -->
      <div class   = "col-12 col-md-6 col-xxl-4 p-2 py-1"
           onclick = "mainCrl.saveDayEntriesBtnClick(event)"
      >
        Save ...
      </div>
<!--
      TASK: now in foods
      
      <div class   = "col-12 col-md-6 col-xxl-4 p-2 py-1"
           onclick = "..."
      >
        Coffee
      </div>

      TASK: fillup now in user > misc.yml

      < ?php if( config::get('special')): ?>
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

  foreach( $this->layout as $tab => $layout )
  {
    // TASK: add food tabs

    foreach( $layout as $groupName => $def )
    {
      if( $groupName == '(first_entries)' || ! ($def['list'] ?? []))  // no entry  // TASK: first_entries currently no use
        continue;
      
      $groupId = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $groupName));

      print $this->inc( __DIR__ . '/group.php', [
        'groupId'   => $groupId,
        'groupName' => $groupName,
        'def'       => $def
      ], $return);

      $done = array_merge( $done, $return['done']);
    }

    // Left over foods

    // $allFoods = array_keys( $this->foodsView->all());
    $allFoods = $this->foodsView->keys();                // foods and recipes are merged in one

    if( count($allFoods) > count( array_unique($done)))  // foods can appear in layout multiple times
    {
      $leftFoods = array_diff( $allFoods, $done );
      $miscFoods = array_filter( $leftFoods, fn($entry) => ! $this->foodsModel->get("$entry.removed"));
      $removed   = array_filter( $leftFoods, fn($entry) =>   $this->foodsModel->get("$entry.removed"));

      // Foods missing in layout (non removed)

      if( count($miscFoods))
      {
        ksort($miscFoods);
        
        print $this->inc( __DIR__ . '/group.php', [
          'groupId'   => lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', 'Misc foods')),
          'groupName' => 'Misc foods',
          'def' => [
            '@attribs' => [
              'short' => null,
              '(i)'   => null,
              // 'color' =>     // currently just default, see group.php
              'fold'  => true
            ],
            'list' => $miscFoods
          ]
        ]);
      }

      // Removed foods

      if( count($removed))
      {
        ksort($removed);
        
        print $this->inc( __DIR__ . '/group.php', [
          'groupId'     => lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', 'Removed')),
          'groupName'   => 'Removed foods',
          'showRemoved' => true,
          'def' => [
            '@attribs' => [
              'short' => null,
              '(i)'   => null,
              // 'color' =>     // currently just default, see group.php
              'fold'  => true
            ],
            'list' => $removed
          ]
        ]);
      }
    }
  }
  
  ?>
</div>
