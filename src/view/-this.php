<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Nutri Counter</title>

  <link href="lib/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="lib/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="lib/jquery-ui-1.13.3/jquery-ui.min.css">
  <link href="style/fixes.css?v=<?= time() ?>" rel="stylesheet">
  <link href="style/theme.css?v=<?= time() ?>" rel="stylesheet">  <!-- TASK: was first to include, why? -->
  <link href="style/app.css?v=<?= time() ?>"   rel="stylesheet">

</head>
<body>
<!--
< ?php
if( error ):  // TASK: also add class "ext" in body
  require( __DIR__ . '/error_page.php');
elseif( ! session_id() ):
  require( __DIR__ . '/login.php');
else:
?>
-->
  <nav class="navbar navbar-expand-lg bg-primary">
    <div class="container-fluid">
      <div class="d-flex w-100 justify-content-between">

        <!-- Simplified -->

        <div class="navbar-brand text-white">  <!-- div for flex align -->

          <i class="bi bi-app"></i>  <!-- some app logo -->

          <select id="userSelect" onchange="mainCrl.userSelectChange(event)" class="bg-transparent border-0 text-white">
            <?php foreach( User::getAll() as $userId ): ?>
              <option value="<?= $userId ?>"<?= self::iif( $userId == User::current('id'), ' selected') ?>>
                <?= User::byId( $userId )->get('name') ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button onclick="mainCrl.switchDayBtnClick(event)"
            data-sel = "<?= $this->mode ?>"
            class    = "btn btn-sm ms-1 mb-1 text-white p-1 py-0 border-light"
          >
            <?php
              $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su'];
              $day = $weekdays[date('D')] . ' ' . date('j') . '.';
            ?>
            <?= self::switch( $this->mode, [
                'current' => $day,
                'last'    => '-1 day',
                'next'    => '+1 day'
              ]) ?? $day
            ?>
          </button>
        </div>
        
        <div class="navbar-brand me-1">   <!-- div for flex align, currently just added a sec navbar-brand and mb for same paddings -->
          <button data-bs-toggle="modal" data-bs-target="#tipsModal" class="btn btn-sm mb-1 text-white" type="button">
            <i class="bi bi-info-circle icon-circle"></i>
          </button>
          <button id="settingsBtn" onclick="mainCrl.settingsBtnClick(event)" class="btn btn-sm mb-1" type="button">
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

    <?php require( __DIR__ . '/main/-this.php'); ?>

  </div>

  <div id="settingsView" class="container-fluid mt-3" style="display: none;">

    <?php require( __DIR__ . '/settings.php'); ?>

  </div>
  
  <?php print $this->renderView( __DIR__ . '/modal/tips.php'); ?>  <!-- prefer new scope when much code -->
  <?php require( __DIR__ . '/modal/help.php'); ?>
  <?php require( __DIR__ . '/modal/about.php'); ?>
  <?php require( __DIR__ . '/modal/new_entry.php'); ?>
  <?php require( __DIR__ . '/modal/info.php'); ?>

  <div id="errorPage" style="display: none;">
    <?php require( __DIR__ . '/error_page.php'); ?>
  </div>

  <?php if( config::get('devMode') ): ?>
    <script>
      document.write('<p>(dev info: ' + window.innerWidth + ' x ' + window.innerHeight + 'px, dpr: ' + window.devicePixelRatio + ')</p>')
    </script>
  <?php endif; ?>

<!--
< ?php endif;  // error_page and login ?>
-->
<script src="lib/jquery-3.7.1.min.js"></script>
<script src="lib/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/jquery-ui-1.13.3/jquery-ui.min.js"></script>
<script src="lib/mermaid-11.3.0.js"></script>
<script src="lib/frm/events_240321.js"></script>
<script src="lib/frm/query_240321.js"></script>
<script src="lib/frm/query_data_240328.js"></script>
<script src="lib/frm/YAMLish_241016.js"></script>
<script src="lib/frm/send_240420.js"></script>
<!-- <script src="lib/frm/fade_230808.js"></script> -->
<script src="MainController.js?v=<?= time() ?>"></script>
<!-- <script src="SettingsController.js?v=<?= time() ?>"></script> -->
<script>

// ajax.file = 'ajax.php'

var dayEntries, mainCrl

ready( function() {

  dayEntries = JSON.parse('<?= json_encode($this->dayEntries) ?>')

  mainCrl = new MainController()
  mainCrl.date = '<?= $this->date ?>'
  mainCrl.updSummary()
})

</script>
</body>
</html>
