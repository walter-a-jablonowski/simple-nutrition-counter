<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>NutriCounter</title>

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> -->
  <link rel="stylesheet" href="style/bootstrap_themed.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  <link href="style/app.css" rel="stylesheet">

</head>
<body>

  <nav class="navbar navbar-expand-lg bg-primary">
    <div class="container-fluid">
      <div class="d-flex w-100 justify-content-between">
        <a class="navbar-brand text-white" href="#">Nutri Counter</a>
<!--
        <div class="d-flex justify-content-center align-items-center">
          <button onclick="foodsCrl.lasrDayBtnClick(event)" class="btn btn-primary mx-2">&lt;</button>
          <div id="dateDisplay" class="mx-2">2024-04-18</div>
          <button onclick="foodsCrl.lastDayBtnClick(event)" class="btn btn-primary mx-2">&gt;</button>
        </div>
-->
        <button type="button" class="btn btn-sm" data-bs-toggle="popover"
                data-bs-title   = "Credits"
                data-bs-content = "BS theme by &lt;a href=&quot;https://bootstrap.build/license&quot;&gt;bootstrap.build&lt;/a&gt;">
          <i class="bi bi-info-circle mt-4 text-white"></i>
        </button>
      </div>
      <!--
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      -->
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav">
        <!--
          <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#import" role="tab">Import</a>
          </li>
        -->
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid mt-3">

    {content}

  </div>

<?php if( $this->debug ): ?>
  <script>
    document.write('<p>Width: ' + window.innerWidth + 'px, Height: ' + window.innerHeight + 'px</p>')
  </script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="lib/events_240321.js"></script>
<script src="lib/query_240321.js"></script>
<script src="lib/query_data_240328.js"></script>
<script src="lib/send_240420.js"></script>
<script src="lib/fade_230808.js"></script>
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
</body>
</html>
