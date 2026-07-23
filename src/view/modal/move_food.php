<div id="moveFoodModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body small">
        <div class="mb-2">Move <b><span id="moveFoodName"></span></b> to:</div>

        <label for="moveFoodGroup" class="form-label mb-1">Group</label>
        <?php print $this->renderView( __DIR__ . '/group_select.php', ['selectId' => 'moveFoodGroup', 'options' => layout_target_options($this->layout)]); ?>

        <label for="moveFoodPos" class="form-label mb-1 mt-2">Position</label>
        <select id="moveFoodPos" class="form-select"></select>
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

<!-- Entries per group, so the position select can be rebuilt without a round trip -->
<script id="moveFoodEntries" type="application/json"><?= json_encode( layout_target_entries($this->layout), JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?></script>
