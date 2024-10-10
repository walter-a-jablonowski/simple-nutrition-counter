<!-- full ui (#code/advancedDayEntries) -->

<div class="scrollable-list">
  <ul id="dayEntries" class="list-group">
    <?php for( $i=0; $i < 4; $i++): ?>
    <!-- < ?php foreach( $this->foodsView->get(...)): ?> -->
      <li class   = "layout-item p-0 list-group-item d-flex justify-content-between align-items-center"
          onclick = ""
          data-type      = "food"
          data-weight    = ""
          data-calories  = ""    v make own function
          data-nutrients = "< ?= html_encode( json_encode( $entry['nutrients'])) ?>"
      >
        <span>
          <span class="handle bi bi-grip-vertical"></span>
          <?= "UI demo food $i" ?>
        </span>
        <div>
          <i class="bi bi-pencil-square btn px-0"></i>
          <button type="button" class="btn">  <!-- maybe add the (-) or make btn in dlg -->
            <i class="bi bi-dash-circle"></i>
          </button>
        </div>
      </li>
    <?php endfor; ?>
  </ul>
</div>
