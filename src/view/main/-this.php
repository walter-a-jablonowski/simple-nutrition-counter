<ul class="nav nav-tabs" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link active py-1 px-2" data-bs-toggle="tab" href="#dayPane" role="tab">
      This day
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a href="#nutrientsPane" class="nav-link py-1 px-2" data-bs-toggle="tab" role="tab">
      Nutrients
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a href="#lastDaysPane" class="nav-link py-1 px-2" data-bs-toggle="tab" role="tab">
      Last days
    </a>
  </li>
<!--
  <li class="nav-item" role="presentation">
    <a href="#foodsPane" class="nav-link py-1 px-2" data-bs-toggle="tab" role="tab">Foods</a>
  </li>
-->
  <li class="nav-item ms-auto" role="presentation">

    <!-- Toggle button for unprecise mode -->
    <button id="unpreciseToggleBtn" onclick="mainCrl.toggleUnpreciseMode(event)" class="btn p-1 border-0 bg-transparent" title="Toggle unprecise mode">
      <i class="bi bi-star-fill<?= $this->isUnprecise ? ' text-warning' : ' text-secondary' ?>"></i>
    </button>

    <!-- Backspace button to delete last line -->
    <button onclick="mainCrl.deleteLastLineBtnClick(event)" class="btn p-1 border-0 bg-transparent">
      <i class="bi bi-backspace"></i>
    </button>

    <!-- TASK: rm currently used save btn -->
    <button onclick="mainCrl.saveDayEntriesBtnClick(event)" class="btn p-1 border-0 bg-transparent">
      <i class="bi bi-floppy"></i>
    </button>

    <button id="expandBtn" class="btn p-1 border-0 bg-transparent">
      <i class="bi bi-caret-down text-secondary"></i>
    </button>
  </li>
</ul>

<div class="tab-content mt-2">
  <div id="dayPane" class="tab-pane fade show active" role="tabpanel">

    <?php require( __DIR__ . '/edit/-this.php'); ?>

  </div>
  <div id="nutrientsPane" class="tab-pane fade" role="tabpanel">

    <?php require( __DIR__ . '/nutrients.php'); ?>

  </div>
  <div id="lastDaysPane" class="tab-pane fade" role="tabpanel">

    <?php require( __DIR__ . '/last_days.php'); ?>

  </div>
</div>
