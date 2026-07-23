<?php

  // devMode menu in the day toolbar (desktop only, see -this.php).
  // Uses the shared drop menu, but closes after a click: these are actions,
  // not toggles. Caller guards this with config::get('devMode').

?>

<div class="drop-menu" data-dir="up" data-close-on-select>

  <button type="button" class="drop-menu-trigger btn p-1 border-0 bg-transparent"
    aria-haspopup = "true"
    aria-expanded = "false"
    aria-label    = "Dev tools"
    title         = "Dev"
  >
    <i class="bi bi-gear"></i>
  </button>

  <div class="drop-menu-panel" role="menu">

    <div class="drop-menu-title">Dev</div>

    <button type="button" role="menuitem" class="drop-menu-item" onclick="mainCrl.openPublishFoods()">
      <i class="bi bi-cloud-arrow-up"></i>
      <span>Publish</span>
    </button>

  </div>
</div>
