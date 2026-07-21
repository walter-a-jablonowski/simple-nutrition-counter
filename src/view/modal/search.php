<!-- Food search overlay: finds a food across all grid tabs and jumps to it.
     Pure client-side lookup over the rendered grid (see MainController #buildFoodIndex). -->

<div id="searchModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Find food</h6>
        <button data-bs-dismiss="modal" class="btn-close" type="button"></button>
      </div>

      <!-- Input bar (sticky) -->
      <div class="modal-body search-body small">
        <div class="input-group mb-2">
          <input id="searchInput" class="form-control" placeholder="Search food … (press Enter)"
                 autocomplete="off" spellcheck="false">
          <button id="searchGoBtn" onclick="mainCrl.runSearch()" class="btn btn-primary" type="button" aria-label="Search">
            <i class="bi bi-search"></i>
          </button>
        </div>

        <!-- Results (filled by js) -->
        <div id="searchResults" class="search-results"></div>
      </div>
    </div>
  </div>
</div>
