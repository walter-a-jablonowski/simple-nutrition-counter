<?php

  // Unprecise drop menu: replaces the former separate toggle buttons.
  // Rendered twice per page, so the items carry data-unprecise instead of ids.
  //
  // $dir  'up' for the desktop toolbar, 'down' for the mobile header

  $items = [
    ['type' => 'nutrients', 'icon' => 'bi-exclamation-circle-fill', 'caption' => 'Nutrients', 'on' => $this->isUnprecise],
    ['type' => 'time',      'icon' => 'bi-stopwatch-fill',          'caption' => 'Time',      'on' => $this->isUnpreciseTime],
    ['type' => 'price',     'icon' => 'bi-currency-euro',           'caption' => 'Price',     'on' => false]  // UI only for now
  ];

  $anyOn = $this->isUnprecise || $this->isUnpreciseTime;
?>

<div class="unprecise-menu<?= $anyOn ? ' any-on' : '' ?>" data-dir="<?= $dir ?>">

  <button type="button" class="unprecise-trigger btn p-1 border-0 bg-transparent"
    aria-haspopup = "true"
    aria-expanded = "false"
    aria-label    = "Unprecise options"
    title         = "Unprecise"
  >
    <i class="bi bi-exclamation-circle-fill"></i>
  </button>

  <div class="unprecise-panel" role="menu">

    <div class="unprecise-title">Unprecise</div>

    <?php foreach( $items as $item ): ?>
      <button type="button" role="menuitemcheckbox"
        class          = "unprecise-item<?= $item['on'] ? ' active' : '' ?>"
        data-unprecise = "<?= $item['type'] ?>"
        aria-checked   = "<?= $item['on'] ? 'true' : 'false' ?>"
        onclick        = "mainCrl.toggleUnprecise(event, '<?= $item['type'] ?>')"
      >
        <i class="bi <?= $item['icon'] ?>"></i>
        <span><?= $item['caption'] ?></span>
      </button>
    <?php endforeach; ?>

  </div>
</div>
