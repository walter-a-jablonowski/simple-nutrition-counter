<!-- Reusable confirm dialog. Drive it via mainCrl.confirm( message, onConfirm, opts) -->

<div id="confirmModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <p id="confirmModalMessage" class="mb-0">Are you sure?</p>
      </div>
      <div class="modal-footer">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Cancel
        </button>
        <button id="confirmModalOkBtn" class="btn btn-sm btn-primary" type="button">
          OK
        </button>
      </div>
    </div>
  </div>
</div>
