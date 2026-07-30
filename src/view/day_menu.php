<?php

  // Day drop menu in both headers (main + favorites layout, see -this.php).
  // Replaces the former switch day button, which cycled through this / -1 / +1
  // day on repeated clicks - the days are picked directly now.
  // Closes after a click: these are navigations, not toggles.
  //
  // The wrapper carries the button classes the old control had (ms-auto and the
  // mobile override in style/app.css), as it is the header's flex item now.

  $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su'];

  // '+1 day', 'This day', '-1 day', '-2 days' ...

  $dayCaption = fn( $offset ) => self::switch( $offset, [1 => '+1 day', 0 => 'This day', -1 => '-1 day'])
                              ?? sprintf('%+d days', $offset);

  $dayDate = fn( DateTime $date ) => $weekdays[$date->format('D')] . ' ' . $date->format('j') . '.';

  $today = new DateTime('today');
  $items = [];

  foreach( range( 1, -5 ) as $offset )   // tomorrow down to 5 days back
  {
    $date = (clone $today)->modify("$offset day");

    $items[] = [
      'date'    => $date->format('Y-m-d'),
      'caption' => $dayCaption( $offset ),
      'day'     => $dayDate( $date )
    ];
  }

  // The trigger shows the date while the current day is open (as before) and
  // the relative caption otherwise - that tells at a glance how far the view is off

  $viewed  = new DateTime( $this->date );
  $offset  = (int) $today->diff( $viewed )->format('%r%a');
  $trigger = $offset === 0 ? $dayDate( $viewed ) : $dayCaption( $offset );
?>

<div class="drop-menu day-menu ms-auto" data-dir="down" data-close-on-select>

  <button type="button" class="drop-menu-trigger btn p-1 py-0 border"
    aria-haspopup = "true"
    aria-expanded = "false"
    aria-label    = "Select day"
    title         = "Day"
  >
    <?= $trigger ?>
  </button>

  <div class="drop-menu-panel" role="menu">

    <div class="drop-menu-title">Day</div>

    <?php foreach( $items as $item ): ?>
      <button type="button" role="menuitem"
        class   = "drop-menu-item<?= self::iif( $item['date'] === $this->date, ' active') ?>"
        onclick = "mainCrl.selectDay(event, '<?= $item['date'] ?>')"
      >
        <span><?= $item['caption'] ?></span>
        <span class="day-menu-date"><?= $item['day'] ?></span>
      </button>
    <?php endforeach; ?>

  </div>
</div>
