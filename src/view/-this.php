<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Nutri Counter</title>

  <link href="style/theme.css" rel="stylesheet">
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> -->
  <link href="lib/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> -->
  <link href="lib/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css"> -->
  <link rel="stylesheet" href="lib/jquery-ui-1.13.3/jquery-ui.min.css">
  <link href="style/fixes.css?v=<?= time() ?>" rel="stylesheet">
  <link href="style/app.css?v=<?= time() ?>"   rel="stylesheet">

</head>
<body>
<!--
< ?php
if( error ):  // TASK: also add class "ext" in body
  require( __DIR__ . '/error_page.php')
elseif( ! session_id() ):
  require( __DIR__ . '/login.php')
else:
?>
-->
  <nav class="navbar navbar-expand-lg bg-primary">
    <div class="container-fluid">
      <div class="d-flex w-100 justify-content-between">

        <!-- Multiple days version -->
<!--
        <div class="d-flex justify-content-center align-items-center">
          <button onclick="foodsCrl.lasrDayBtnClick(event)" class="btn btn-primary mx-2">&lt;</button>
          <div id="dateDisplay" class="mx-2">2024-04-18</div>
          <button onclick="foodsCrl.lastDayBtnClick(event)" class="btn btn-primary mx-2">&gt;</button>
        </div>
-->
        <!-- Simplified -->

        <div class="text-white">  <!-- removed navbar-brand padding and stuff only -->

          <i class="bi bi-egg-fried"></i>  <!-- some app logo -->

          <select id="userSelect" class="bg-transparent border-0 text-white">
            <?php foreach( $this->users as $user => $name ): ?>
              <option value="<?= $user ?>"<?= self::iif( $user == $this->user, ' selected') ?>>
                <?= $name ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button onclick="foodsCrl.lastDayBtnClick(event)"
            data-sel = "<?= $this->mode ?>"
            class    = "btn btn-sm btn-secondary mb-1"
          >
            <?php
              $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su'];
              $day = $weekdays[date('D')] . ' ' . date('j') . '.';
            ?>
            <?= self::iif( $this->mode === 'current', $day, "-1 day") ?>
          </button>
        </div>
        <div>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tipsModal">
            <i class="bi bi-info-circle icon-circle"></i>
          </button>
          <button id="settingsBtn" onclick="foodsCrl.settingsBtnClick(event)" type="button" class="btn btn-sm">
            <i class="bi bi-gear-fill text-white"></i>
          </button>
        </div>
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

  <div id="mainView" class="container-fluid mt-3" style="display: block;">  <!-- needed -->

    <?php require( __DIR__ . '/tabs/-this.php') ?>

  </div>

  <div id="settingsView" class="container-fluid mt-3" style="display: none;">

    <?php require( __DIR__ . '/settings.php') ?>

  </div>

  <?php require( __DIR__ . '/modal/tips.php') ?>
  <?php require( __DIR__ . '/modal/help.php') ?>
  <?php require( __DIR__ . '/modal/about.php') ?>
  <?php require( __DIR__ . '/modal/new_entry.php') ?>
  <?php require( __DIR__ . '/modal/info.php') ?>

  <div id="errorPage" style="display: none;">
    <?php require( __DIR__ . '/error_page.php') ?>
  </div>

  <?php if( $this->devMode ): ?>
    <script>
      document.write('<p>(dev info: ' + window.innerWidth + ' x ' + window.innerHeight + 'px, dpr: ' + window.devicePixelRatio + ')</p>')
    </script>
  <?php endif; ?>

<!--
< ?php endif;  // error_page and login ?>
-->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- <script src="lib/jquery-3.6.0.min.js"></script> -->
<script src="lib/jquery-3.7.1.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script> -->
<script src="lib/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<!-- <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script> -->
<script src="lib/jquery-ui-1.13.3/jquery-ui.min.js"></script>
<script src="lib/events_240321.js"></script>
<script src="lib/query_240321.js"></script>
<script src="lib/query_data_240328.js"></script>
<script src="lib/YAMLish_240508.js"></script>
<script src="lib/send_240420.js"></script>
<script src="lib/fade_230808.js"></script>
<script src="controller.js?v=<?= time() ?>"></script>
<script>

// ajax.file = 'ajax.php'

var dayEntries, foodsCrl

ready( function() {

  dayEntries = [  // will be replaced by #code/advancedData
    <?php foreach( $this->dayEntries as $entry): ?>
      {food: '<?= $entry[0] ?>', calories: <?= $entry[1] ?>, fat: <?= $entry[2] ?>, carbs: <?= $entry[3] ?>, amino: <?= $entry[4] ?>, salt: <?= $entry[5] ?>, price: <?= $entry[6] ?>, nutrients: <?= ( ! $entry[7] ? '{}' : dump_json($entry[7])) ?>},
    <?php endforeach; ?>
  ]

  foodsCrl = new FoodsEventController()
  foodsCrl.date = '<?= $this->date ?>'
  foodsCrl.updSummary()
})

</script>
</body>
</html>
