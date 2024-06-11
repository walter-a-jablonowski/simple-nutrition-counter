<div class="row">
  <div class="col">

    <?php require( __DIR__ . '/day_entries.php') ?>
    <?php require( __DIR__ . '/quick_summary.php') ?>

  </div>
</div>

<div class="row mt-2">
  <div class="col scrollable-list">

    <?php
    
      if( ! $this->devMode ):
        require( __DIR__ . '/food_list.php');
      else:
        require( __DIR__ . '/food_list_new.php');  // also use one line in controller (see "for debugging new layout")
      endif;

    ?>

  </div>
</div>
