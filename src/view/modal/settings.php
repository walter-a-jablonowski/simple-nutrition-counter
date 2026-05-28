<div id="settingsModal" class="modal info-modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 p-0 px-3 pt-3">
        <ul class="nav nav-tabs w-100 border-0" role="tablist">
          <li class="nav-item" role="presentation">
            <a href="#settingsMePane" class="nav-link active py-1 px-2 small" data-bs-toggle="tab" role="tab">
              Me
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a href="#settingsFoodsPane" class="nav-link py-1 px-2 small" data-bs-toggle="tab" role="tab">
              Foods
            </a>
          </li>
          <li class="nav-item ms-auto" role="presentation">
            <button data-bs-dismiss="modal" class="btn-close" type="button"></button>
          </li>
        </ul>
      </div>
      <div class="modal-body pt-2">

        <div class="tab-content">
          <div id="settingsMePane" class="tab-pane fade show active" role="tabpanel">

            <p class="text-secondary small mb-2">
              <?= htmlspecialchars( $this->inlineHelp->get('app.settings.me')) ?>
            </p>

            <?php if( config::get('devMode') ): ?>
              <ul class="list-group">
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

          </div>
          <div id="settingsFoodsPane" class="tab-pane fade" role="tabpanel">

            <?php if( config::get('devMode') ): ?>
              <ul class="list-group">
                <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
                  Foods
                </li>
                <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
                  Recipes
                </li>
                <li class="list-group-item px-1" onclick="" style="cursor: pointer;">
                  Layout
                </li>
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

          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Cancel
        </button>
        <button data-bs-dismiss="modal" class="btn btn-sm btn-primary" type="button">
          Save
        </button>
      </div>
    </div>
  </div>
</div>
