<div id="moveFoodModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body small">
        <div class="mb-2">Move <b><span id="moveFoodName"></span></b> to:</div>
        <?php print $this->renderView( __DIR__ . '/group_select.php', ['selectId' => 'moveFoodGroup', 'options' => layout_target_options($this->layout)]); ?>
      </div>
      <div class="modal-footer py-2">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Cancel
        </button>
        <button id="moveFoodConfirmBtn" onclick="mainCrl.moveFoodConfirm()" class="btn btn-sm btn-primary" type="button">
          Move
        </button>
      </div>
    </div>
  </div>
</div>
