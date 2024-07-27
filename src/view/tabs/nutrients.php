
<?php if( config::get('devMode') ): ?>
  <ul class="list-group mt-3">
    <li class="list-group-item px-2 py-1 small">
      <div class="form-check">
        <input id="offLimitCheck" type="checkbox" value="" class="form-check-input">
        <label class="form-check-label small" for="offLimitCheck">
          off limit only
        </label>
      </div>
    </li>
  </ul>
<?php endif; ?>

<div class="scrollable-list border-0 mt-3">

  <!-- TASK: collapse or expand all btn -->

  <?php foreach( $this->foodsView->nutrients as $groupShort => $group ): ?>

    <ul class="list-group">

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >
        <?= $this->captions[$groupShort] ?>
        <a data-bs-toggle="collapse" href="#<?= $groupShort ?>Collapse" class="text-body-secondary" role="button">
          <i class="bi bi-arrow-down-circle"></i>
        </a>
      </li>
      <li id="<?= $groupShort ?>Collapse" class="list-group-item collapse show">

        <?php foreach( $group as $short => $data ): ?>

          <div class     = "nutrients-entry mb-2"
              data-group = "<?= $groupShort ?>"
              data-short = "<?= $short ?>"
              data-ideal = "<?= $data['ideal'] ?>"
          >
            <div><?= $data['name'] ?></div>  <!-- TASK: or right align 80 / 100 -->
            <div class="progress w-100" role="progressbar">
              <div class="progress-bar bg-success" style="width: 0%;">
                <span><!-- < ?= $data['name'] ?> --><span class="progress-label">0 / <?= $data['ideal'] ?></span></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

      </li>
    </ul>
  <?php endforeach; ?>
</div>
