<?php if( $this->devMode ): ?>
  <ul class="list-group">
    <li class="list-group-item">
      <span class="fw-bold">Edit</span> (advanced feature)
    </li>
    <li class="list-group-item" onclick="" style="cursor: pointer;">
      Foods
    </li>
    <li class="list-group-item" onclick="" style="cursor: pointer;">
      Recipes
    </li>
    <li class="list-group-item" onclick="" style="cursor: pointer;">
      Layout
    </li>
<!--
    <li class="list-group-item" onclick="" style="cursor: pointer;">
      Expenses
    </li>
-->
    <li class="list-group-item" onclick="" style="cursor: pointer;">
      Misc counters<br>
      <span style="font-size: 0.6rem;">Expenses</span>
    </li>
    <li class="list-group-item" onclick="" style="cursor: pointer;">
      Nutrients
    </li>
    <li class="list-group-item" onclick="" style="cursor: pointer;">
      Nutrient groups
    </li>
  </ul>
<?php endif; ?>

<ul class="list-group mt-3">
  <li class="list-group-item" data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">
    Help
  </li>
  <li class="list-group-item fw-bold" data-bs-toggle="modal" data-bs-target="#aboutModal" style="cursor: pointer;">
    About
  </li>
</ul>

<button onclick="foodsCrl.settingsBtnClick(event)" class="btn btn-sm btn-primary mt-3">
  Done
</button>
