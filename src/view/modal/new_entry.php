<!-- AI generated -->

<div id="newEntryModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
<!--
      <div class="modal-header">
        <h6 class="modal-title">New entry</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
-->
      <div class="modal-body small">
        
        <!-- Floating form (placeholder is more compact) -->
        <!-- below version has is more up 2 date -->
<!--
        <div class="form-floating mb-3">
          <input id="modalNameInput" class="form-control" placeholder="Name" value="Misc" required>
          <label for="modalNameInput">Name</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalCaloriesInput" type="number" inputmode="numeric" class="form-control" placeholder="Calories" required>
          <label for="modalCaloriesInput">Calories</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalFatInput"   type="number" inputmode="numeric" class="form-control" placeholder="Fat (g)" required>
          <label for="modalFatInput">Fat (g)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalCarbsInput" type="number" inputmode="numeric" class="form-control" placeholder="Carbs (g)" required>
          <label for="modalCarbsInput">Carbs (g)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalAminoInput" type="number" inputmode="numeric" class="form-control" placeholder="Amino Acids (g)" required>
          <label for="modalAminoInput">Amino Acids (g)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalSaltInput"  type="number" inputmode="numeric" class="form-control" placeholder="Salt (g)" required>
          <label for="modalSaltInput">Salt (g)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalPriceInput"  type="number" inputmode="numeric" class="form-control" placeholder="Price" required>
          <label for="modalPriceInput">Price</label>
        </div>
-->
        <div class="mb-2">
          <input id="modalNameInput" placeholder="Name" value="Misc entry" class="form-control" required>
        </div>
        <div class="row mb-2">
          <div class="col">
            <input id="modalWeightInput" type="number" inputmode="numeric" placeholder="Weight (g)" class="form-control" required>
          </div>
          <div class="col">
            <input id="modalPiecesInput" type="number" inputmode="numeric" placeholder="Pieces" class="form-control">
          </div>
        </div>
        <select id="modalUsedSelect" class="form-select mb-2" required>
          <option class="default" value="null" disabled selected>Used amount ...</option>
          <!-- TASK: get from defaults -->
          <option data-usage="pack"    value="0.25">1/4</option>
          <option data-usage="pack"    value="0.33">1/3</option>
          <option data-usage="pack"    value="0.5" >1/2</option>
          <option data-usage="pack"    value="1"   >1</option>
          <option data-usage="pieces"  value="1"   >1 pc</option>
          <option data-usage="pieces"  value="2"   >2 pc</option>
          <option data-usage="pieces"  value="3"   >3 pc</option>
          <option data-usage="precise" value="50"  >50g or ml</option>
          <option data-usage="precise" value="100" >100g or ml</option>
        </select>
        <div class="mb-2">
          <div class="input-group">
            <input id="modalCaloriesInput" type="number" inputmode="numeric" placeholder="Calories" class="form-control" required>
            <span class="input-group-text">kcal</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <input id="modalFatInput"   type="number" inputmode="numeric" step="0.1" placeholder="Fat (g)" class="form-control" required>
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <input id="modalCarbsInput" type="number" inputmode="numeric" step="0.1" placeholder="Carbs (g)" class="form-control">
            <span class="input-group-text">g</span>
          </div>
        </div>
        <!-- TASK: (very advanced) some list where non-required can be added (fibre and all nutrients -->
        <div class="mb-2">
          <div class="input-group">
            <input id="modalFibreInput" type="number" inputmode="numeric" step="0.1" placeholder="Fibre (g)" class="form-control">
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <input id="modalAminoInput" type="number" inputmode="numeric" step="0.1" placeholder="Amino Acids (g)" class="form-control" required>
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <input id="modalSaltInput"  type="number" inputmode="numeric" step="0.1" placeholder="Salt (g)" class="form-control" required>
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input id="modalPriceInput"  type="number" inputmode="numeric" step="0.01" placeholder="Price" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="d-flex w-100 justify-content-between">
          <!-- TASK: add a dev config and hide -->
          <!-- TASK: (very advanced) -->
        <div>
          <div class="form-check">
            <input id="flexCheckDefault" type="checkbox" value="" class="form-check-input">
            <label class="form-check-label small" for="flexCheckDefault">
              Save as new food
            </label>
          </div>
        </div>
        <div>
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="button" class="btn btn-sm btn-primary" onclick="foodsCrl.newEntrySaveBtn()">
            Add entry
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
