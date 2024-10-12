<div id="layout" class="row">

  <span id="uiMsg"></span>  <!-- TASK: mov somewhere outside tab or one msg per tab (or use a toast) -->


  <!-- Tab content (only if more than one) -->

  <?php if( count($this->layout) > 1 ): ?>
    <div class="col-12 mt-2">
      <div class="overflow-auto">
        <ul class="nav nav-pills flex-nowrap">  <!-- flex is for scrolling -->
          <?php $i=0; foreach( $this->layout as $tab => $layout ): ?>
            <?php
              $i++;
              $tabId = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $tab));
            ?>
            <li class="nav-item">
              <a class="nav-link px-2 py-1 text-nowrap overflow-hidden<?= self::iif( $i === 1, ' active') ?>" data-bs-toggle="tab" href="#<?= $tabId ?>LayoutPane" role="tab"><?= $tab ?></a>
            </li>
          <?php endforeach; ?>
          <li class="nav-item ms-auto">
            <a onclick="mainCrl.newEntryBtn(event)" class="nav-link px-2 py-1 text-black" role="tab">
              <i class="bi bi-pencil-square"></i>
            </a>
          </li>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <!-- TASK: add a collapse expand all (single btn) -->


  <!-- TASK: (advanced) MOV also buyings here (maybe use some select that changes sub forms in the new form) -->

  <!-- Groups (and tab content) -->

  <div class="col-12 tab-content mt-1">
    
    <?php

    $done = [];  $i=0;

    foreach( $this->layout as $tab => $layout )
    {
      $i++;

      // Start tab content (only if more than one)

      if( count($this->layout) > 1 )
      {
        $tabId  = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $tab));
        $active = $i === 1 ? ' show active' : '';
        
        print trim("
          \n<div id=\"{$tabId}LayoutPane\" class=\"tab-pane fade$active\" role=\"tabpanel\">
            <div class=\"row\">
        ");
      }

      // TASK: single group above full width with ho header see (first_entries)
      //       add in done and exclude below
      //       special and fillup now in user > misc.yml
    
      // Group

      foreach( $layout as $groupName => $def )
      {
        if( $groupName == '(first_entries)' || ! ($def['list'] ?? []))  // no entry  // TASK: first_entries currently no use
          continue;
        
        $groupId = lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', $groupName));

        print $this->renderView( __DIR__ . '/group.php', [
          'groupId'   => $groupId,
          'groupName' => $groupName,
          'def'       => $def
        ], $return);

        $done = array_merge( $done, $return['done']);
      }

      // Left over entries (add in first tab)

      if( count($this->layout) === 1 )
      {
        // $allEntries = array_keys( $this->layoutView->all());
        $allEntries = $this->layoutView->keys();               // foods and recipes are merged in one

        if( count($allEntries) > count( array_unique($done)))  // entries can appear in layout multiple times
        {
          $leftEntries = array_diff( $allEntries, $done );
          $miscEntries = array_filter( $leftEntries, fn($entry) => ! $this->foodsModel->get("$entry.removed"));
          $removed   = array_filter( $leftEntries, fn($entry) =>   $this->foodsModel->get("$entry.removed"));

          // Entries missing in layout (non removed)

          if( count($miscEntries))
          {
            ksort($miscEntries);
            
            print $this->renderView( __DIR__ . '/group.php', [
              'groupId'   => lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', 'Misc')),
              'groupName' => 'Misc',
              'def' => [
                '@attribs' => [
                  'short' => null,
                  '(i)'   => null,
                  // 'color' =>     // currently just default, see group.php
                  'fold'  => true
                ],
                'list' => $miscEntries
              ]
            ]);
          }

          // Removed entries

          if( count($removed))
          {
            ksort($removed);
            
            print $this->renderView( __DIR__ . '/group.php', [
              'groupId'     => lcfirst( preg_replace('/[^a-zA-Z0-9]/', '', 'Removed')),
              'groupName'   => 'Removed',
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

      // End tab content (only if more than one)

      if( count($this->layout) > 1 )
      {
        print trim("
          \n  </div>
          </div>
        ");
      }
    }

    ?>

  </div>
</div>