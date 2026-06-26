<?php

extract($args);

$return['done'] = [];

?><div class="col-12">

  <div class="row">
    <?php foreach( $def['list'] as $idx => $entryName ): ?>
      <div class="col-12 col-md-6 col-xxl-4">  <!-- group col (no mt-2: keep tight stacking like regular groups) -->

        <div class="p-1">  <!-- match the .collapse.p-1 wrapper used in group.php so layout-item rows
                                here have the same border-box width + text positions as in regular groups -->
          <?php

            if( ! ($args['showRemoved'] ?? false) && $this->combinedModel->get("$entryName.state") === 'removed')
              continue;  // no removed entries, even if in layout (see group Removed in UI)

            print $this->renderView( __DIR__ . '/entry.php', [
              'entryName' => $entryName
            ], $return);

          ?>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
</div>
