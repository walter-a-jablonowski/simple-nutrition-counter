<?php

extract($args);

$return['done'] = [];

?><div class="col-12 mt-2">  <!-- group col -->

  <div class="row">
    <div class="col-6">

      <?php

      foreach( $def['list'] as $idx => $entryName )
      {
        if( ! ($args['showRemoved'] ?? false) && $this->foodsModel->get("$entryName.removed"))
          continue;  // no removed entries, even if in layout (see group Removed in UI)

        print $this->renderView( __DIR__ . '/entry.php', [
          'entryName' => $entryName
        ], $return);
      }

      ?>

    </div>
  </div>
</div>
