<!-- AI generated -->

<div id="newEntryModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">New entry</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body small">
        
        <!-- Floating form (placeholder is more compact) -->
        <!-- below version has more fields: weight, used, fibre -->
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
          <input id="modalFatInput"   type="number" inputmode="numeric" class="form-control" placeholder="Fat (grams)" required>
          <label for="modalFatInput">Fat (grams)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalCarbsInput" type="number" inputmode="numeric" class="form-control" placeholder="Carbs (grams)" required>
          <label for="modalCarbsInput">Carbs (grams)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalAminoInput" type="number" inputmode="numeric" class="form-control" placeholder="Amino Acids (grams)" required>
          <label for="modalAminoInput">Amino Acids (grams)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalSaltInput"  type="number" inputmode="numeric" class="form-control" placeholder="Salt (grams)" required>
          <label for="modalSaltInput">Salt (grams)</label>
        </div>
        <div class="form-floating mb-3">
          <input id="modalPriceInput"  type="number" inputmode="numeric" class="form-control" placeholder="Price" required>
          <label for="modalPriceInput">Price</label>
        </div>
-->
        <div class="mb-3">
          <input id="modalNameInput" placeholder="Name" value="Misc" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalWeightInput" type="number" inputmode="numeric" placeholder="Weight" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalUsedInput" type="number" inputmode="numeric" placeholder="Used amount" class="form-control" required>
        </div>
        <select id="modalUsedSelect" class="form-select">
          <optgroup data-usage="pack" label="Fractions">
            <option value="0.25">1/4</option>
            <option value="0.33">1/3</option>
            <option value="0.5">1/2</option>
            <option value="1">1</option>
          </optgroup>
          <optgroup data-usage="pieces" label="Pieces">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
          </optgroup>
          <optgroup data-usage="precise" label="Precise">
            <option value="50">50g</option>
            <option value="100">100g</option>
          </optgroup>
        </select>
        <div class="mb-3">
          <input id="modalCaloriesInput" type="number" inputmode="numeric" placeholder="Calories" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalFatInput"   type="number" inputmode="numeric" placeholder="Fat (grams)" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalCarbsInput" type="number" inputmode="numeric" placeholder="Carbs (grams)" value="0" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalFibreInput" type="number" inputmode="numeric" placeholder="Fibre (grams)" class="form-control">
        </div>
        <div class="mb-3">
          <input id="modalAminoInput" type="number" inputmode="numeric" placeholder="Amino Acids (grams)" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalSaltInput"  type="number" inputmode="numeric" placeholder="Salt (grams)" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalPriceInput"  type="number" inputmode="numeric" placeholder="Price" class="form-control" required>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
        <button type="button" class="btn btn-sm btn-primary" onclick="foodsCrl.newEntrySaveBtn()">
          Save
        </button>
      </div>
    </div>
  </div>
</div>
