<!-- TASK: need a container? -->

<?php require( __DIR__ . '/day_entries.php'); ?>
<?php require( __DIR__ . '/quick_summary.php'); ?>

<div class="row mt-2">
  <div class="col scrollable-list">  <!-- TASK: or just a div, no grid ? h-100 -->

    <?php require( __DIR__ . '/food_list/-this.php'); ?>

  </div>
</div>
