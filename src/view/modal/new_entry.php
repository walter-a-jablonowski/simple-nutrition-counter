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
        <div class="mb-3">
          <input id="modalNameInput" placeholder="Name" value="Misc entry" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalWeightInput" type="number" inputmode="numeric" placeholder="Weight (g)" class="form-control" required>
        </div>
<!--
        <div class="mb-3">
          <input id="modalUsedInput" type="number" inputmode="numeric" placeholder="Used amount" class="form-control" required>
        </div>
-->
        <select id="modalUsedSelect" class="form-select" required>
          <option class="default" value="null" disabled selected>Used amount ...</option>
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
        <div class="mb-3">
          <input id="modalCaloriesInput" type="number" inputmode="numeric" placeholder="Calories (kcal)" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalFatInput"   type="number" inputmode="numeric" step="0.1" placeholder="Fat (g)" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalCarbsInput" type="number" inputmode="numeric" step="0.1" placeholder="Carbs (g)" value="0" class="form-control">
        </div>
        <div class="mb-3">
          <input id="modalFibreInput" type="number" inputmode="numeric" step="0.1" placeholder="Fibre (g)" class="form-control">
        </div>
        <div class="mb-3">
          <input id="modalAminoInput" type="number" inputmode="numeric" step="0.1" placeholder="Amino Acids (g)" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalSaltInput"  type="number" inputmode="numeric" step="0.1" placeholder="Salt (g)" class="form-control" required>
        </div>
        <div class="mb-3">
          <input id="modalPriceInput"  type="number" inputmode="numeric" step="0.01" placeholder="Price" class="form-control">
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
