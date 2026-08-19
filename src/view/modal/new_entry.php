<div id="newEntryModal" class="modal fade" tabindex="-1">
  <!-- scrollable: long content scrolls inside the modal body, so the modal
       itself never overflows the viewport and adds a scrollbar to the page -->
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">New entry</h6>
        <!-- TASK: add a dev config and hide -->
        <div class="ms-auto d-flex gap-2">
          <?php if( config::get('photoImport.enabled') ): ?>
            <button id="imgShowBtn" onclick="mainCrl.showPanel('photo')" class="btn btn-sm btn-outline-secondary" type="button" title="Read a packaging photo">
              <i class="bi bi-camera"></i>
            </button>
          <?php endif; ?>
          <button id="importShowBtn" onclick="mainCrl.showPanel('import')" class="btn btn-sm btn-outline-secondary" type="button">
            Import
          </button>
        </div>
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
        <!-- Entry / new-food form -->
        <div id="newEntryFormPanel">
        <!-- What an import could not read or got suspicious about (photo import) -->
        <div id="importWarnMsg" class="importWarnMsg small d-none"></div>
        <ul class="nav modalTabs" role="tablist">
          <li class="nav-item">
            <button id="entryTab" class="nav-link active" data-bs-toggle="tab" data-bs-target="#entryTabPane" type="button" role="tab">Entry</button>
          </li>
          <li class="nav-item">
            <button id="detailsTab" class="nav-link" data-bs-toggle="tab" data-bs-target="#detailsTabPane" type="button" role="tab">Details</button>
          </li>
        </ul>
        <div class="tab-content">
        <div id="entryTabPane" class="tab-pane fade show active" role="tabpanel">
        <div class="mb-2">
          <input id="modalNameInput" placeholder="Name" value="Misc entry" class="form-control modalHilitePrimary" required>
        </div>
        <div class="row mb-2">
          <div class="col">
            <div class="input-group">
              <input id="modalWeightInput" type="number" inputmode="numeric" placeholder="Weight" class="form-control" required>
              <select id="modalWeightUnit" class="form-select flex-grow-0 w-auto">
                <option value="g">g</option>
                <option value="ml">ml</option>
              </select>
            </div>
          </div>
          <div class="col">
            <input id="modalPiecesInput" type="number" inputmode="numeric" placeholder="Pieces" class="form-control modalHiliteSecondary">
          </div>
        </div>
        <!-- The two selects below decide what saving does: an amount logs a day
             entry, a group creates a food record. Either one, or both; the
             footer button says which. -->
        <!-- How much is consumed right now (logs a day entry) -->
        <select id="modalUsedSelect" class="form-select mb-2 modalHilitePrimary">
          <option class="default" value="null" selected>Consumed now ...</option>
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
        <!-- Where a new food record goes in the grid. Picking a group is what
             creates the record; the placeholder means "no food record". -->
        <?php print $this->renderView( __DIR__ . '/group_select.php', ['selectId' => 'modalTargetGroup', 'extraClass' => 'mb-2 modalHilitePrimary', 'placeholder' => 'Save as food ...', 'options' => layout_target_options($this->layout)]); ?>
        <!-- Typical amounts shown as clickable columns in the food grid
             (saved as usedAmounts, food record only). Precise options adapt to
             the weight unit. -->
        <select id="modalUsedAmountsSelect" class="form-select mb-2 modalHilitePrimary">
          <option value="" selected>Grid amounts (default) ...</option>
          <optgroup label="Precise" id="modalPreciseAmounts">
            <option data-type="precise" value="25,50,100">25g / 50g / 100g</option>
            <option data-type="precise" value="50,100,200">50g / 100g / 200g</option>
            <option data-type="precise" value="10,20,30">10g / 20g / 30g</option>
            <option data-type="precise" value="5,10,15">5g / 10g / 15g</option>
            <option data-type="precise" value="10,25,50">10g / 25g / 50g</option>
          </optgroup>
          <optgroup label="Fractions">
            <option data-type="pack" value="1/2,1,2">1/2 / 1 / 2</option>
            <option data-type="pack" value="1/8,1/4,1/3">1/8 / 1/4 / 1/3</option>
            <option data-type="pack" value="1/4,1/2,1">1/4 / 1/2 / 1</option>
            <option data-type="pack" value="1/4,1/3,1/2">1/4 / 1/3 / 1/2</option>
            <option data-type="pack" value="1/8,1/4,1/2">1/8 / 1/4 / 1/2</option>
          </optgroup>
          <optgroup label="Pieces">
            <option data-type="pieces" value="1">1</option>
            <option data-type="pieces" value="1,2,3">1 / 2 / 3</option>
            <option data-type="pieces" value="1,2,4">1 / 2 / 4</option>
            <option data-type="pieces" value="1,2,5">1 / 2 / 5</option>
          </optgroup>
        </select>
        <div class="mb-2">
          <div class="input-group">
            <span class="input-group-text modalNutrientLabel">Calories</span>
            <input id="modalCaloriesInput" type="number" inputmode="numeric" class="form-control" required>
            <span class="input-group-text">kcal</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <span class="input-group-text modalNutrientLabel">Fat</span>
            <input id="modalFatInput"   type="number" inputmode="numeric" step="0.1" class="form-control" required>
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <span class="input-group-text modalNutrientLabel">Sat. fat</span>
            <input id="modalSatFatInput" type="number" inputmode="numeric" step="0.1" class="form-control">
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <span class="input-group-text modalNutrientLabel">Carbs</span>
            <input id="modalCarbsInput" type="number" inputmode="numeric" step="0.1" class="form-control">
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <span class="input-group-text modalNutrientLabel">Sugar</span>
            <input id="modalSugarInput" type="number" inputmode="numeric" step="0.1" class="form-control">
            <span class="input-group-text">g</span>
          </div>
        </div>
        <!-- TASK: (very advanced) some list where non-required can be added (fibre and all nutrients -->
        <div class="mb-2">
          <div class="input-group">
            <span class="input-group-text modalNutrientLabel">Fibre</span>
            <input id="modalFibreInput" type="number" inputmode="numeric" step="0.1" class="form-control">
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <span class="input-group-text modalNutrientLabel">Amino acids</span>
            <input id="modalAminoInput" type="number" inputmode="numeric" step="0.1" class="form-control" required>
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="input-group">
            <span class="input-group-text modalNutrientLabel">Salt</span>
            <input id="modalSaltInput"  type="number" inputmode="numeric" step="0.1" class="form-control" required>
            <span class="input-group-text">g</span>
          </div>
        </div>
        <div class="row">
          <div class="col">
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input id="modalPriceInput"  type="number" inputmode="numeric" step="0.01" placeholder="Price" class="form-control modalHilitePrimary">
            </div>
          </div>
          <div class="col">
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input id="modalDealPriceInput" type="number" inputmode="numeric" step="0.01" placeholder="Deal price" class="form-control">
            </div>
          </div>
        </div>
        </div><!-- /entryTabPane -->

        <!-- Details tab: food-record metadata (mostly filled by import) -->
        <div id="detailsTabPane" class="tab-pane fade" role="tabpanel">
          <div class="mb-2">
            <input id="modalProductNameInput" placeholder="Product name (exact)" class="form-control">
          </div>
          <div class="mb-2">
            <input id="modalUrlInput" type="url" placeholder="URL" class="form-control">
          </div>
          <div class="input-group mb-2">
            <span class="input-group-text modalDetailLabel">Acceptable</span>
            <select id="modalAcceptableSelect" class="form-select modalHilitePrimary">
              <option value="">always ok</option>
              <option value="occasionally">occasionally</option>
              <option value="less">less (avoid)</option>
            </select>
          </div>
          <div class="input-group mb-2">
            <span class="input-group-text modalDetailLabel">NutriScore</span>
            <input id="modalNutriScoreInput" maxlength="1" placeholder="A–E" class="form-control">
            <span class="input-group-text modalHiliteCheck">
              <input id="modalVeganCheck" type="checkbox" class="form-check-input mt-0 me-1">vegan
            </span>
            <span class="input-group-text">
              <input id="modalBioCheck" type="checkbox" class="form-check-input mt-0 me-1">bio
            </span>
          </div>
          <div class="mb-2">
            <textarea id="modalIngredientsInput" rows="3" placeholder="Ingredients" class="form-control"></textarea>
          </div>
          <div class="mb-2">
            <input id="modalAllergyInput" placeholder="Allergy (e.g. Enthält: …)" class="form-control">
          </div>
          <div class="mb-2">
            <input id="modalMayContainInput" placeholder="May contain (e.g. Kann Spuren von …)" class="form-control">
          </div>
          <div>
            <input id="modalPackagingInput" placeholder="Packaging (e.g. cardboard,alu,plastic)" class="form-control modalHilitePrimary">
          </div>
        </div><!-- /detailsTabPane -->
        </div><!-- /tab-content -->
        </div><!-- /newEntryFormPanel -->
        <!-- Import panel: fetch a food from a product page (URL) or pasted HTML -->
        <div id="newEntryImportPanel" class="d-none">
          <div class="mb-2 small text-secondary">
            Paste a product page URL, or paste the page's HTML if the vendor blocks direct access.
          </div>
          <div class="mb-2">
            <input id="importUrlInput" type="url" placeholder="Product page URL" class="form-control">
          </div>
          <div class="mb-2">
            <textarea id="importHtmlInput" rows="5" placeholder="… or paste page HTML here" class="form-control"></textarea>
          </div>
          <div id="importMsg" class="small text-danger mb-2"></div>
          <div class="d-flex justify-content-between">
            <button onclick="mainCrl.showPanel('form')" class="btn btn-sm btn-secondary" type="button">
              Back
            </button>
            <button id="importRunBtn" onclick="mainCrl.importRunBtn()" class="btn btn-sm btn-primary" type="button">
              Import
            </button>
          </div>
        </div>

        <!-- Photo panel: read a food off pictures of the packaging (Gemini vision).
             The browser downscales the shots, the limits come from config.yml -->
        <?php if( config::get('photoImport.enabled') ): ?>
        <div id="newEntryPhotoPanel" class="d-none"
             data-max-images="<?= (int)   (config::get('photoImport.maxImages') ?: 3) ?>"
             data-max-edge="<?=   (int)   (config::get('photoImport.maxEdge')   ?: 1568) ?>"
             data-quality="<?=    (float) (config::get('photoImport.quality')   ?: 0.82) ?>">
          <div class="mb-2 small text-secondary">
            Photograph the pack: front, nutrition table, ingredients. Hold the camera straight
            and let the table fill the frame.
          </div>
          <div class="d-flex gap-2 mb-2">
            <label for="photoCameraInput" class="btn btn-sm btn-outline-secondary mb-0">
              <i class="bi bi-camera"></i> Take picture
            </label>
            <label for="photoFileInput" class="btn btn-sm btn-outline-secondary mb-0">
              <i class="bi bi-images"></i> Choose
            </label>
          </div>
          <!-- capture opens the camera but hides the gallery on android, so the
               second input is the one that works on a desktop -->
          <input id="photoCameraInput" type="file" accept="image/*" capture="environment" multiple class="d-none">
          <input id="photoFileInput"   type="file" accept="image/*" multiple class="d-none">
          <div id="photoList" class="d-flex flex-wrap gap-2 mb-2"></div>
          <div id="photoMsg" class="small text-danger mb-2"></div>
          <div class="d-flex justify-content-between">
            <button onclick="mainCrl.showPanel('form')" class="btn btn-sm btn-secondary" type="button">
              Back
            </button>
            <button id="photoRunBtn" onclick="foodPhotoCrl.run()" class="btn btn-sm btn-primary" type="button">
              Read pictures
            </button>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <div id="newEntryFooter" class="modal-footer">
        <div class="d-flex w-100 justify-content-end align-items-center gap-2">
          <!-- Validation hint: hover the "!" for the message (space is tight) -->
          <span id="modalSaveError" class="modalSaveError d-none" title="">!</span>
          <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
            Cancel
          </button>
          <!-- Label follows the two selects above (see #updSaveBtnLabel) -->
          <button id="newEntrySaveBtn" onclick="mainCrl.newEntrySaveBtn()" class="btn btn-sm btn-primary" type="button">
            Add entry
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
