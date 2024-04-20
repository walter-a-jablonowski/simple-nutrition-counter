<ul class="nav nav-tabs" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link active py-1 px-2 small" data-bs-toggle="tab" href="#inpPane" role="tab">This day</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#dayPane" role="tab">Summary</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#lastDaysPane" role="tab">Last days</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#foodsPane" role="tab">Foods</a>
  </li>
</ul>

<div class="tab-content mt-3">
  <div class="tab-pane fade show active" id="inpPane" role="tabpanel">

    {edit_tab}

  </div>
  <div class="tab-pane fade" id="dayPane" role="tabpanel">

    {nutrients_tab}

  </div>
  <div class="tab-pane fade" id="lastDaysPane" role="tabpanel">

    {days_tab}

  </div>
  <div class="tab-pane fade" id="foodsPane" role="tabpanel">

    {foods_tab}

  </div>
</div>

<?php if( $this->debug ): ?>
  <script>
    document.write('<p>Width: ' + window.innerWidth + 'px, Height: ' + window.innerHeight + 'px</p>')
  </script>
<?php endif; ?>

<script src="controller.js"></script>
<script>

// ajax.file = 'ajax.php'

var dayEntries, foodsCrl

ready( function() {

  dayEntries = [  // will be replaced by #code/advancedData
    <?php foreach( $this->model->dayEntries as $entry): ?>
      {food: '<?= $entry[0] ?>', calories: <?= $entry[1] ?>, fat: <?= $entry[2] ?>, amino: <?= $entry[3] ?>, salt: <?= $entry[4] ?>},
    <?php endforeach; ?>
  ]

  foodsCrl = new FoodsEventController()
  foodsCrl.updSums()
})

</script>
