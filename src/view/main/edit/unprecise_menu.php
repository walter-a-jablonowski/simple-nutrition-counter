<?php

  // Unprecise drop menu: replaces the former separate toggle buttons.
  // Rendered twice per page, so the items carry data-unprecise instead of ids.
  // 'flag' is the day file header the entry writes, see dev_info/Misc.md
  //
  // $dir  'up' for the desktop toolbar, 'down' for the mobile header

  $items = [
    ['flag' => 'unprecise',      'icon' => 'bi-exclamation-circle-fill', 'caption' => 'Nutrients', 'on' => $this->isUnprecise],
    ['flag' => 'unpreciseTime',  'icon' => 'bi-stopwatch-fill',          'caption' => 'Time',      'on' => $this->isUnpreciseTime],
    ['flag' => 'unprecisePrice', 'icon' => 'bi-currency-euro',           'caption' => 'Price',     'on' => $this->isUnprecisePrice]
  ];

  $anyOn = $this->isUnprecise || $this->isUnpreciseTime || $this->isUnprecisePrice;
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
        data-unprecise = "<?= $item['flag'] ?>"
        aria-checked   = "<?= $item['on'] ? 'true' : 'false' ?>"
        onclick        = "mainCrl.toggleUnprecise(event, '<?= $item['flag'] ?>')"
      >
        <i class="bi <?= $item['icon'] ?>"></i>
        <span><?= $item['caption'] ?></span>
      </button>
    <?php endforeach; ?>

  </div>
</div>
