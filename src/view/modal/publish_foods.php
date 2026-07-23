<!-- Publish the food data to the installation folder (devMode, see tools/publish_foods).
     Two steps: "Check" reports what would change, "Publish" applies it. -->

<div id="publishFoodsModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header py-2">
        <h6 class="modal-title">Publish food data</h6>
        <button data-bs-dismiss="modal" class="btn-close" type="button" aria-label="Close"></button>
      </div>

      <div class="modal-body small">
        <div class="text-secondary mb-2">
          Compares by content hash, not by file date, and copies only what really changed.
        </div>

        <pre id="publishFoodsReport" class="publish-report mb-2">Click "Check" to see what would change.</pre>

        <div class="form-check">
          <input id="publishFoodsDelete" class="form-check-input" type="checkbox">
          <label for="publishFoodsDelete" class="form-check-label">
            Also delete obsolete files at the destination
          </label>
        </div>
      </div>

      <div class="modal-footer py-2">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Close
        </button>
        <button id="publishFoodsCheckBtn" onclick="mainCrl.publishFoodsCheck()" class="btn btn-sm btn-outline-secondary" type="button">
          Check
        </button>
        <button id="publishFoodsRunBtn" onclick="mainCrl.publishFoodsRun()" class="btn btn-sm btn-primary" type="button" disabled>
          Publish
        </button>
      </div>
    </div>
  </div>
</div>
