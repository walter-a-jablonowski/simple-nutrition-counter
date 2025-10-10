
<?php if( config::get('devMode') ): ?>
  <ul class="list-group mt-3">
    <li class="list-group-item px-2 py-1 small d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center py-1">

        <div class="dropdown me-3">
          <button id="" class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <span class="fw-bold">This day</span>
          </button>
          <ul class="dropdown-menu" onclick="">  <!-- Last days tab has only: Last week, 15 days, 30 days -->
            <li><a class="dropdown-item small" data-value="" href="#">This day</a></li>
            <li><a class="dropdown-item small" data-value="" href="#">This week</a></li>
            <li><a class="dropdown-item small" data-value="" href="#">Last week</a></li>
            <li><a class="dropdown-item small" data-value="" href="#">Last 2 weeks</a></li>
            <li><a class="dropdown-item small" data-value="" href="#">7 days</a></li>
            <li><a class="dropdown-item small" data-value="" href="#">15 days</a></li>
            <li><a class="dropdown-item small" data-value="" href="#">30 days</a></li>
          </ul>
        </div>

        <div class="form-check form-switch me-3 mb-0">
          <input id="offLimitCheck" onchange="mainCrl.offLimitCheckChange(event)" type="checkbox" role="switch" class="form-check-input" style="margin-top: 5px;">
          <label class="form-check-label small" for="offLimitCheck">
            off limit only
          </label>
        </div>
        <button id="sportsToggleBtn" onclick="mainCrl.sportsToggleBtnClick(event)"
                class = "btn btn-sm sports-toggle-btn"
                title = "If you did sports"
                type  = "button"
        >
          Sports
        </button>
      </div>
      <button type="button" class="border-0 p-1 bg-transparent"
              data-bs-toggle = "modal"
              data-bs-target = "#infoModal"
              data-title     = "How to read nutrients"
              data-source    = "#nutrientsData"
              style          = "color: #e95420;"
      >
        <i class="bi bi-info-circle icon-circle"></i> Info
      </button>
      <div id="nutrientsData" class="d-none">
        <?php require('misc/inline_nutrients.php'); ?>
      </div>
    </li>
  </ul>
<?php endif; ?>

<div id="nutrientsList" class="scrollable-list border-0">

  <!-- TASK: collapse or expand all btn -->

  <?php foreach( $this->nutrientsView->all() as $shortName => $group ): ?>

    <ul class="list-group">

      <li class = "list-group-item d-flex justify-content-between align-items-center mt-2"
          style = "background-color: #e0e0e0;"
      >
        <?= $this->captions[$shortName] ?>
        <a data-bs-toggle="collapse" href="#<?= $shortName ?>Collapse" class="text-body-secondary" role="button">
          <i class="bi bi-arrow-down-circle"></i>
        </a>
      </li>
      <li id="<?= $shortName ?>Collapse" class="list-group-item collapse show">

        <?php foreach( $group as $short => $data ):
        
          $unit = $data['unit'] ?? $group['defaultUnit'] ?? 'g';
        ?>

          <div class = "nutrients-entry mb-2"

               data-bs-toggle = "modal"
               data-bs-target = "#infoModal"
               data-title     = "Foods"
               data-source    = "#<?= $short ?>Data"

               data-group     = "<?= $shortName ?>"
               data-short     = "<?= $short ?>"
               data-lower     = "<?= $data['lower'] ?>"
               data-ideal     = "<?= $data['ideal'] ?>"
               data-upper     = "<?= $data['upper'] ?>"
               data-current   = "0"
               data-unit      = "<?= $unit ?>"
          >
            <div class="row">
              <div class="col text-nowrap overflow-hidden">
                <?= self::iif( $data['displayName'], $data['displayName'], $data['name']) ?>
                <i class="bi bi-info-circle ms-1" style="color: #bfbfbf; font-size: 0.8em;"></i>
              </div>
              <div class="col-4 text-secondary text-end small text-nowrap overflow-hidden">
                <span class="vals">0 / <?= $data['ideal'] ?></span> <?= $unit ?>
              </div>
              <div class="col-3 text-end">
                <span class="percent">0</span>%
              </div>
            </div>
            <div class="progress w-100" role="progressbar">
              <div class="progress-bar bg-secondary" style="width: 0%;">
                <!-- <span><span class="progress-label">0 / < ?= $data['ideal'] ?></span></span> -->
              </div>
            </div>
          </div>
          <div id="<?= $short ?>Data" class="d-none">
            &nbsp;la
          </div>
        <?php endforeach; ?>

      </li>
    </ul>
  <?php endforeach; ?>
</div>
