<!-- AI generated -->

<div class="modal fade" id="newEntryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="modalNameInput" class="form-label">Name</label>
            <input id="modalNameInput"  type="text" value="Misc" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="modalCaloriesInput" class="form-label">Calories</label>
            <input id="modalCaloriesInput"  type="number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="modalFatInput" class="form-label">Fat (grams)</label>
            <input id="modalFatInput"  type="number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="modalAminoInput" class="form-label">Amino Acids (grams)</label>
            <input id="modalAminoInput"  type="number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="modalSaltInput" class="form-label">Salt (grams)</label>
            <input id="modalSaltInput"  type="number" class="form-control" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" onclick="foodsCrl.newEntrySaveBtn()">
          Save
        </button>
      </div>
    </div>
  </div>
</div>
