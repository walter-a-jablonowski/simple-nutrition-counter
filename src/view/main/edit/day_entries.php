<!-- Day entries: sortable list (replaces the old #dayEntries textarea).
     Each li carries all entry fields in data-* attributes; the list is the
     single source of truth (MainController rebuilds the dayEntries array from it). -->

<ul id="dayEntriesList" class="day-entries list-group list-group-flush flex-grow-1">

  <?php foreach( $this->dayEntries as $entry ): ?>
    <li class="day-entry list-group-item d-flex align-items-center px-2 py-1"
        data-type     = "<?= htmlspecialchars( $entry['type'],     ENT_QUOTES) ?>"
        data-food     = "<?= htmlspecialchars( $entry['food'],     ENT_QUOTES) ?>"
        data-time     = "<?= htmlspecialchars( $entry['time'],     ENT_QUOTES) ?>"
        data-calories = "<?= htmlspecialchars( $entry['calories'], ENT_QUOTES) ?>"
        data-fat      = "<?= htmlspecialchars( $entry['fat'],      ENT_QUOTES) ?>"
        data-carbs    = "<?= htmlspecialchars( $entry['carbs'],    ENT_QUOTES) ?>"
        data-amino    = "<?= htmlspecialchars( $entry['amino'],    ENT_QUOTES) ?>"
        data-salt     = "<?= htmlspecialchars( $entry['salt'],     ENT_QUOTES) ?>"
        data-price    = "<?= htmlspecialchars( $entry['price'],    ENT_QUOTES) ?>"
        data-nutrients = "<?= htmlspecialchars( dump_json( $entry['nutrients']), ENT_QUOTES) ?>"
    >
      <span class="day-entry-type"><?= htmlspecialchars( $entry['type'], ENT_QUOTES) ?></span>

      <div class="day-entry-main flex-grow-1 ms-2 overflow-hidden">
        <div class="day-entry-name text-truncate"><?= htmlspecialchars( $entry['food'], ENT_QUOTES) ?></div>
        <div class="day-entry-sub small text-secondary d-flex">
          <span class="day-entry-time"><?= htmlspecialchars( substr( $entry['time'], 0, 5), ENT_QUOTES) ?></span>
          <span class="day-entry-amount"><?= htmlspecialchars( $entry['nutrients']['amount']['label'] ?? '', ENT_QUOTES) ?></span>
        </div>
      </div>

      <button type="button" onclick="mainCrl.deleteEntryBtnClick(event)" class="day-entry-del btn p-1 border-0 bg-transparent text-secondary" aria-label="Delete entry">
        <i class="bi bi-x-lg"></i>
      </button>
    </li>
  <?php endforeach; ?>

</ul>

<div id="dayEntriesEmpty" class="day-entries-empty text-secondary small text-center p-3<?= empty($this->dayEntries) ? '' : ' d-none' ?>">
  No entries yet
</div>
