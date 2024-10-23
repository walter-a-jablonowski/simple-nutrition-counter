<?php

extract($args);

$return['done'] = [];

?><div class="col-12 mt-2">

  <div class="row">
    <?php foreach( $def['list'] as $idx => $entryName ): ?>
      <div class="col-12 col-md-6 col-xxl-4 mt-2">  <!-- group col -->

        <?php

          if( ! ($args['showRemoved'] ?? false) && $this->foodsModel->get("$entryName.removed"))
            continue;  // no removed entries, even if in layout (see group Removed in UI)

          print $this->renderView( __DIR__ . '/entry.php', [
            'entryName' => $entryName
          ], $return);

        ?>

      </div>
    <?php endforeach; ?>
  </div>
</div>
