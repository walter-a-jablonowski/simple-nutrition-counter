<div id="tipsModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
<!--
      <div class="modal-header">
        <h6 class="modal-title">Tips</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
-->
      <div class="modal-body small">

        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active py-1 px-2 small" data-bs-toggle="tab" href="#appPane" role="tab">App help</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#nutritionPane" role="tab">Nutrition tips</a>
          </li>
        </ul>

        <div class="tab-content mt-3">
          <div id="appPane" class="tab-pane fade show active" role="tabpanel">

            <?= file_get_contents('misc/tips_app.html') ?>

          </div>
          <div id="nutritionPane" class="tab-pane fade" role="tabpanel">

            <?= file_get_contents('misc/tips_nutrition.html') ?>

          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Close
        </button>
      </div>
    </div>
  </div>
</div>
