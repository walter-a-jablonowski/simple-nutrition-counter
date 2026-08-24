<?php

  // Fold menu right of the food grid search button: folds / unfolds every group
  // header at once. A single entry flips between "Fold all" and "Unfold all" -
  // MainController sets the caption on open (#updFoldMenu) and performs it
  // (#toggleFoldAll). Rendered once per page (left- vs right-handed variant).
  // Closes after the click: this is an action, not a toggle row like unprecise.
  // The trigger reuses the neighbouring icon link classes (nav-link text-black),
  // so it reads exactly like the search / new entry buttons.

?>

<li class="nav-item">
  <div class="drop-menu fold-menu h-100" data-dir="down" data-close-on-select>

    <a class="drop-menu-trigger nav-link px-2 py-1 text-black" href="#"
      aria-haspopup = "true"
      aria-expanded = "false"
      aria-label    = "Group fold options"
      title         = "Groups"
      onclick       = "mainCrl.updFoldMenu()"
    >
      <i class="bi bi-three-dots-vertical"></i>
    </a>

    <div class="drop-menu-panel" role="menu">

      <button type="button" role="menuitem" class="drop-menu-item"
        onclick = "mainCrl.toggleFoldAll(event)"
      >
        <i id="foldAllIcon" class="bi"></i>
        <span id="foldAllLabel">Fold all</span>
      </button>

    </div>
  </div>
</li>
