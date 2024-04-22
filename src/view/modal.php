<!-- AI generated -->

<div class="modal fade" id="newEntryModal" tabindex="-1" aria-labelledby="newEntryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newEntryModalLabel">New entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="modalNameInput" class="form-label">Name</label>
            <input type="text" class="form-control" id="modalNameInput" value="Misc" required>
          </div>
          <div class="mb-3">
            <label for="modalCaloriesInput" class="form-label">Calories</label>
            <input type="number" class="form-control" id="modalCaloriesInput" required>
          </div>
          <div class="mb-3">
            <label for="modalFatInput" class="form-label">Fat (grams)</label>
            <input type="number" class="form-control" id="modalFatInput" required>
          </div>
          <div class="mb-3">
            <label for="modalAminoInput" class="form-label">Amino Acids (grams)</label>
            <input type="number" class="form-control" id="modalAminoInput" required>
          </div>
          <div class="mb-3">
            <label for="modalSaltInput" class="form-label">Salt (grams)</label>
            <input type="number" class="form-control" id="modalSaltInput" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="foodsCrl.newEntrySaveBtn()">Save</button>
      </div>
    </div>
  </div>
</div>
