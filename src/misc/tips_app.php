<?php if( config::get('devMode') ): ?>
  <b>Edit settings first (see <i class="bi bi-arrow-right"></i> <i class="bi bi-gear-fill"></i></b>)

  <ul class="no-indent">
    <li>used for calculating the right nutrient amounts</li>
  </ul>
<?php endif; ?>

<b>Date switch (beside app title)</b>

<ul class="no-indent">
  <li>click to switch to last, next or back to current day</li>
  <li>enables adding entries to last calendar day (for night owls)</li>
  <li>or go to the next day first</li>
</ul>

<b>App usage</b>

<ul class="no-indent">
  <!-- <li>On this day's tab you see the list of consumed items first (above or left), then a bar with primary nutrition goals and current sums, below controls for entering foods ...</li> -->
  <li>
    On this day's tab you see
    <ul class="no-indent">
      <li>the list of consumed items first (above or left)</li>
      <li>then a bar with primary nutrition goals and current sums</li>
      <li>below controls for entering foods ...</li>
    </ul>
  </li>
  <li>On the Nutrients tab you can see all nutrient's state in case you want to fill up one with a supplement</li>
</ul>

<b>Food list</b>

<ul class="no-indent">
  <li>foods that can be used occasionally are highlighted <span class="p-1 py-0" style="background-color: #ffff88;">yellow</span></li>
  <li>less good foods are highlighted <span class="p-1 py-0" style="background-color: #ffcccc;">red</span></li>
  <li>
    <i class="bi bi-currency-euro small text-secondary"></i> or
    <i class="bi bi-currency-dollar small text-secondary"></i>
    highlights expensive foods
  </li>
  <li>
    <i class="bi bi-currency-exchange small text-secondary"></i>
    is cheap food (depends on user setting)
  </li>
  <li>
    <!-- <i class="bi bi-info-circle small text-secondary"></i> -->
    <i class="bi bi-info-circle-fill small" style="color: orange;"></i>
    means important things in food info
  </li>
  <li>click on each food name to see food info</li>
</ul>
