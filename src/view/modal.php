<!-- AI generated -->

<div class="modal fade" id="newEntryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <div class="form-floating mb-3">
          <input id="modalNameInput" type="text" class="form-control" placeholder="Name" value="Misc" required>
          <label for="modalNameInput">Name</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalCaloriesInput" type="number" class="form-control" placeholder="Calories" required>
          <label for="modalCaloriesInput">Calories</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalFatInput"   type="number" class="form-control" placeholder="Fat (grams)" required>
          <label for="modalFatInput">Fat (grams)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalAminoInput" type="number" class="form-control" placeholder="Amino Acids (grams)" required>
          <label for="modalAminoInput">Amino Acids (grams)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalSaltInput"  type="number" class="form-control" placeholder="Salt (grams)" required>
          <label for="modalSaltInput">Salt (grams)</label>
        </div>
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
