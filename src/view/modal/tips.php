<div id="tipsModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable" style="height: 100vh;">
    <div class="modal-content" style="height: 90%;">
<!--
      <div class="modal-header">
        <h6 class="modal-title">Tips</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
-->
      <div class="modal-body small">

        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <a href="#tipsAppPane" class="nav-link active py-1 px-2 small" style="color: #888 !important;" data-bs-toggle="tab" role="tab">
              App help
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a href="#tipsNutritionPane" class="nav-link py-1 px-2 small" style="color: #888 !important;" data-bs-toggle="tab" role="tab">
              Nutrition tips
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a href="#tipsMissionPane" class="nav-link py-1 px-2 small" style="color: #888 !important;" data-bs-toggle="tab" role="tab">
              Mission
            </a>
          </li>
          <li class="nav-item ms-auto" role="presentation">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </li>
        </ul>

        <div class="tab-content mt-3">
          <div id="tipsAppPane" class="tab-pane fade show active" role="tabpanel">

            <?= file_get_contents('misc/tips_app.html') ?>

          </div>
          <div id="tipsNutritionPane" class="tab-pane fade" role="tabpanel">

            <?= file_get_contents('misc/tips_nutrition.html') ?>

          </div>
          <div id="tipsMissionPane" class="tab-pane fade" role="tabpanel">

            <?= file_get_contents('misc/tips_mission.html') ?>

          </div>
        </div>

      </div>
<!--
      <div class="modal-footer">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Close
        </button>
      </div>
-->
    </div>
  </div>
</div>
