<ul class="list-group">
  <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
    <div class="row">
      <label for="userSelect" class="col-sm-2 col-form-label fw-bold">User</label>
      <div class="col-sm-10 col-md-3">
        <select id="userSelect" class="form-select">
          <?php foreach( $this->users as $user ): ?>
            <option value="<?= $user ?>"<?= self::iif( $user == $this->user, ' selected') ?>>
              <?= $user ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </li>
</ul>

<?php if( $this->devMode ): ?>
  <ul class="list-group">
    <li class="list-group-item px-1">
      <span class="fw-bold">Me</span>
      <button type="button" class="btn btn-sm text-secondary" data-bs-toggle="popover"
              data-bs-content = "<?= htmlspecialchars( $this->inlineHelp->get('settings.me') ) ?>">
        <i class="bi bi-info-circle mt-4"></i>
      </button>
      (advanced feature)
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      <div class="row">
        <label for="genderSelect" class="col-sm-2 col-form-label">Gender</label>
        <div class="col-sm-10 col-md-3">
          <select id="genderSelect" class="form-select">
            <option value="male">male</option>
            <option value="female">female</option>
          </select>
        </div>
      </div>
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      <div class="row">
        <label for="birthYearSelect" class="col-sm-2 col-form-label">Birth</label>
        <div class="col-sm-3 col-md-2">
          <select id="birthYearSelect" class="form-select">
            <option class="default" value="null" disabled selected>year</option>
            <option value="">2024</option>
          </select>
        </div>
        <div class="col-sm-3">
          <select id="birthMonthSelect" class="form-select">
            <option class="default" value="null" disabled selected>month</option>
            <option value="">11</option>
          </select>
        </div>
        <div class="col-sm-3 col-md-2">
          <select id="birthDaySelect" class="form-select">
            <option class="default" value="null" disabled selected>day</option>
            <option value="">01</option>
          </select>
        </div>
      </div>
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      <div class="row">
        <label for="heightInp" class="col-sm-2 col-form-label">Height</label>
        <div class="col-sm-10 col-md-3">
          <div class="input-group">
            <input id="heightInp" type="number" class="form-control form-control-sm">
            <span class="input-group-text">cm</span>
          </div>
        </div>
      </div>
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      <div class="row">
        <label for="weightInp" class="col-sm-2 col-form-label">Weight</label>
        <div class="col-sm-10 col-md-3">
          <div class="input-group">
            <input id="weightInp" type="number" class="form-control form-control-sm">
            <span class="input-group-text">kg</span>
          </div>
        </div>
      </div>
    </li>
  </ul>
<?php endif; ?>

<?php if( $this->devMode ): ?>
  <ul class="list-group">
    <li class="list-group-item px-1">
      <span class="fw-bold">Foods</span> (advanced feature)
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      Foods
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      Recipes
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      Layout
    </li>
<!--
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      Expenses
    </li>
-->
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      Misc counters<br>
      <span style="font-size: 0.6rem;">Expenses</span>
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      Nutrients
    </li>
    <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
      Nutrient groups
    </li>
  </ul>
<?php endif; ?>

<ul class="list-group mt-3">
  <li class="list-group-item px-1 fw-bold" data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">
    Help
  </li>
  <li class="list-group-item px-1 fw-bold" data-bs-toggle="modal" data-bs-target="#aboutModal" style="cursor: pointer;">
    About
  </li>
</ul>

<button onclick="foodsCrl.settingsBtnClick(event)" class="btn btn-sm btn-primary mt-3">
  Done
</button>
