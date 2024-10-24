<?php

extract($args);

$return['done'] = [];

?><div class="col-12 col-md-6 col-xxl-4 mt-2">  <!-- group col -->

  <div class="row">
    <div class="col-12">  <!-- below outer container for the bg color (would be full width without) -->
      <div id    = "<?= $groupId ?>Group"
           class = "p-1 px-2 fw-bold d-flex justify-content-between align-items-center"
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

  <div id="<?= $groupId ?>Collapse" class="p-1 collapse<?= self::iif( ! ($def['@attribs']['fold'] ?? false), ' show') ?>">

    <?php if( isset($def['@attribs']['short'])): ?>
      <div class="row mt-1 mb-1">
        <div class = "col-12 px-2 small">
          &nbsp;<?= $def['@attribs']['short'] ?>  <!-- simple spacer -->
        </div>
      </div>
    <?php endif; ?>

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
