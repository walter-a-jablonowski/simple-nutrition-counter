<ul class="nav nav-tabs" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link active py-1 px-2 small" data-bs-toggle="tab" href="#inpPane" role="tab">This day</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#dayPane" role="tab">Summary</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#lastDaysPane" role="tab">Last days</a>
  </li>
<!--
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#foodsPane" role="tab">Foods</a>
  </li>
-->
  <li class="nav-item ms-auto" role="presentation">
    <button id="expandBtn" class="btn p-1 border-0 bg-transparent">
      <i class="bi bi-caret-down text-secondary"></i>
    </button>
  </li>
</ul>

<div class="tab-content mt-3">
  <div class="tab-pane fade show active" id="inpPane" role="tabpanel">

    <?php require( __DIR__ . '/edit/-this.php') ?>

  </div>
  <div class="tab-pane fade" id="dayPane" role="tabpanel">

    <?php require( __DIR__ . '/nutrients.php') ?>

  </div>
  <div class="tab-pane fade" id="lastDaysPane" role="tabpanel">

    <?php require( __DIR__ . '/last_days.php') ?>

  </div>
<!--
  <div class="tab-pane fade" id="foodsPane" role="tabpanel">

    < ?php require( __DIR__ . '/foods.php') ?>

  </div>
-->
</div>
