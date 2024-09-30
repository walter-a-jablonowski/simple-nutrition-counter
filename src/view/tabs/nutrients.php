
<?php if( config::get('devMode') ): ?>
  <ul class="list-group mt-3">
    <li class="list-group-item px-2 py-1 small">
      <div class="form-check">
        <input id="offLimitCheck" onchange="mainCrl.offLimitCheckChange(event)" type="checkbox" value="" class="form-check-input">
        <label class="form-check-label small" for="offLimitCheck">
          off limit only
        </label>
      </div>
    </li>
  </ul>
<?php endif; ?>

<div id="nutrientsList" class="scrollable-list border-0 mt-3">

  <!-- TASK: collapse or expand all btn -->

  <?php foreach( $this->nutrientsView->all() as $shortName => $group ): ?>

    <ul class="list-group">

      <li class = "list-group-item d-flex justify-content-between align-items-center"
          style = "background-color: #e0e0e0;"
      >
        <?= $this->captions[$shortName] ?>
        <a data-bs-toggle="collapse" href="#<?= $shortName ?>Collapse" class="text-body-secondary" role="button">
          <i class="bi bi-arrow-down-circle"></i>
        </a>
      </li>
      <li id="<?= $shortName ?>Collapse" class="list-group-item collapse show">

        <?php foreach( $group as $short => $data ): ?>

          <div class      = "nutrients-entry mb-2"
               data-group = "<?= $shortName ?>"
               data-short = "<?= $short ?>"
               data-lower = "<?= $data['lower'] ?>"
               data-ideal = "<?= $data['ideal'] ?>"
               data-upper = "<?= $data['upper'] ?>"
               data-current = "0"
          >
            <div class="d-flex justify-content-between">  <!-- align-items-center justify-content-center -->
              <span><?= $data['name'] ?></span>
              <span>
                <span class="vals text-secondary small">0/<?= $data['ideal'] ?></span>
                <span class="percent">0</span>%
              </span>
            </div>
            <div class="progress w-100" role="progressbar">
              <div class="progress-bar bg-secondary" style="width: 0%;">
                <!-- <span><span class="progress-label">0 / < ?= $data['ideal'] ?></span></span> -->
              </div>
            </div>
          </div>
        <?php endforeach; ?>

      </li>
    </ul>
  <?php endforeach; ?>
</div>
