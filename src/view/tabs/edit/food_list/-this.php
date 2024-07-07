<div id="foodList" class="row">

  <span id="uiMsg"></span>  <!-- TASK: mov -->

  <!-- TASK: add a collapse expand all (single btn) -->

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

  foreach( $this->layout as $groupName => $def )
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

      // array_merge( array_keys( $this->modelView->get('recipes')),  // TASK
  $all = array_keys( $this->modelView->get('foods'));

  if( count($all) > count( array_unique($done)))  // foods can appear in layout multiple times
  {
    $def = [
      '@attribs' => [
        'short' => null,
        '(i))'  => null,
        // 'color' =>     // currently just default, see group.php
        'fold'  => true
      ],
      'list'  => array_diff( $all, $done)
    ];

    ksort($def['list']);
    
    print $this->inc( __DIR__ . '/group.php', [
      'groupId'   => lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', 'Misc foods')),
      'groupName' => 'Misc foods',
      'def'       => $def
    ]);
  }
  
  ?>
</div>
