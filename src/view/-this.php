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
  <link href="style/charts.css?v=<?= time() ?>" rel="stylesheet">

</head>
<body class="<?= User::current('settings.hideScrollbars') ? 'no-scrollbars' : '' ?>">
<!--
< ?php
if( error ):  // TASK: also add class "ext" in body
  require( __DIR__ . '/error_page.php');
elseif( ! session_id() ):
  require( __DIR__ . '/login.php');
else:
?>
-->

  <!-- Sidebar (landscape / desktop) -->

  <nav id="sidebar" class="navbar navbar-dark bg-dark d-none d-md-flex flex-column">
    <div class="container-fluid p-0 h-100 d-flex flex-column justify-content-between">

      <!-- Top: section switcher -->

      <ul class="navbar-nav">
        <li class="nav-item">
          <a data-nav="day" class="nav-link active" href="#" title="This day">
            <i class="bi bi-calendar-day"></i>
          </a>
        </li>
        <li class="nav-item">
          <a data-nav="nutrients" class="nav-link" href="#" title="Nutrients">
            <i class="bi bi-cup-straw"></i>
          </a>
        </li>
        <li class="nav-item">
          <a data-nav="charts" class="nav-link" href="#" title="Charts">
            <i class="bi bi-graph-up"></i>
          </a>
        </li>
        <li class="nav-item">
          <a data-nav="favorites" class="nav-link" href="#" title="Last days">
            <i class="bi bi-calendar-range"></i>
          </a>
        </li>
      </ul>

      <!-- Bottom: info + settings -->

      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="#" title="Info"
            data-bs-toggle = "modal"
            data-bs-target = "#tipsModal"
          >
            <i class="bi bi-info-circle"></i>
          </a>
        </li>
        <li class="nav-item">
          <a id="settingsBtn" class="nav-link" href="#" title="Settings"
            data-bs-toggle = "modal"
            data-bs-target = "#settingsModal"
          >
            <i class="bi bi-person"></i>
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Bottom nav (portrait / mobile) -->

  <nav id="bottomNav" class="navbar navbar-dark bg-dark fixed-bottom d-md-none">
    <div class="container-fluid px-2">
      <div class="nav-content">
        <a data-nav="day" class="nav-link active" href="#" title="This day">
          <i class="bi bi-calendar-day"></i>
        </a>
        <a data-nav="nutrients" class="nav-link" href="#" title="Nutrients">
          <i class="bi bi-cup-straw"></i>
        </a>
        <a data-nav="charts" class="nav-link" href="#" title="Charts">
          <i class="bi bi-graph-up"></i>
        </a>
        <!--
        <a data-nav="favorites" class="nav-link" href="#" title="Last days">
          <i class="bi bi-calendar-range"></i>
        </a>
        -->
        <a class="nav-link" href="#" title="Info"
          data-bs-toggle = "modal"
          data-bs-target = "#tipsModal"
        >
          <i class="bi bi-info-circle"></i>
        </a>
        <a class="nav-link" href="#" title="Settings"
          data-bs-toggle = "modal"
          data-bs-target = "#settingsModal"
        >
          <i class="bi bi-person"></i>
        </a>
      </div>
    </div>
  </nav>

  <!-- Main content -->

  <main id="mainView" class="app-d3 container-fluid h-100 p-0 d-flex flex-column">

    <div id="mainLayout" class="row g-0 flex-grow-1 h-100">

      <!-- Left column (day textarea + action buttons) -->

      <div class="left-column col-md-2 h-100">
        <div class="content-wrapper h-100 overflow-auto d-flex flex-column">

          <!-- Header -->

          <header class="bg-light border-bottom py-2 px-2 text-break fs-5 d-flex align-items-center">
            <div class="d-flex align-items-center">

              <?php require( __DIR__ . '/app_logo.php'); ?>

              <select onchange="mainCrl.userSelectChange(event)" class="js-userSelect border-0 fs-5">
                <?php foreach( User::getAll() as $userId ): ?>
                  <option value="<?= $userId ?>"<?= self::iif( $userId == User::current('id'), ' selected') ?>>
                    <?= User::byId( $userId )->get('name') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Day switch button: ms-auto pushes it to the right of the logo/name group.
                 On mobile it stays next to the name instead and .header-actions
                 takes the free space (see the mobile block in style/app.css). -->
            <?php
              $weekdays = ['Mon' => 'Mo', 'Tue' => 'Tu', 'Wed' => 'We', 'Thu' => 'Th', 'Fri' => 'Fr', 'Sat' => 'Sa', 'Sun' => 'Su'];
              $day = $weekdays[date('D')] . ' ' . date('j') . '.';
            ?>
            <button onclick="mainCrl.switchDayBtnClick(event)"
              data-sel = "<?= $this->mode ?>"
              class    = "js-switchDayBtn btn ms-auto p-1 py-0 border"
            >
              <?= self::switch( $this->mode, [
                  'current' => $day,
                  'last'    => '-1 day',
                  'next'    => '+1 day'
                ]) ?? $day
              ?>
            </button>

            <!-- Mobile action buttons -->

            <div class="header-actions d-flex align-items-center gap-3 ms-3 d-md-none">

              <?php $dir = 'down'; require( __DIR__ . '/main/edit/unprecise_menu.php'); ?>

              <!-- Deleting saves right away, so there is no save button -->
              <button onclick="mainCrl.deleteLastLineBtnClick(event)" class="btn p-1 border-0 bg-transparent" aria-label="Delete">
                <i class="bi bi-backspace" aria-hidden="true"></i>
              </button>

              <button id="expandBtn" class="mobile-caret-btn btn p-1" aria-label="Toggle view">
                <i class="bi bi-caret-down" aria-hidden="true"></i>
              </button>
            </div>
          </header>

          <!-- Day entries (sortable list); scrolls internally so header + toolbar stay put -->

          <section class="day-entries-section flex-grow-1 mt-2 d-flex flex-column">

            <?php require( __DIR__ . '/main/edit/day_entries.php'); ?>

          </section>

          <!-- Desktop action buttons -->

          <div class="d-none d-md-flex justify-content-center gap-3 py-2 px-3" role="toolbar">

            <!-- Unprecise drop-up menu -->
            <?php $dir = 'up'; require( __DIR__ . '/main/edit/unprecise_menu.php'); ?>

            <!-- Backspace button to delete last line; every list change saves
                 right away (see #afterListChange), so there is no save button -->
            <button onclick="mainCrl.deleteLastLineBtnClick(event)" class="btn p-1 border-0 bg-transparent">
              <i class="bi bi-backspace"></i>
            </button>

            <?php if( config::get('devMode') ): ?>
              <?php require( __DIR__ . '/main/edit/dev_menu.php'); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right column (widgets + content panes) -->

      <div class="right-column col-md-10 h-100">
        <div class="content-wrapper h-100 overflow-auto d-flex flex-column">

          <?php require( __DIR__ . '/main/edit/quick_summary.php'); ?>

          <!-- Food grid -->

          <section class="food-grid flex-grow-1 pb-2 px-3">
            <div class="row g-3">
              <div class="col-12">

                <div id="dayContent">

                  <?php require( __DIR__ . '/main/edit/layout/-this.php'); ?>

                </div>

                <div id="nutrientsContent" class="d-none">

                  <?php require( __DIR__ . '/main/nutrients.php'); ?>

                </div>

                <!-- Moved to #favoritesLayout -->
<!--
                <div id="lastDaysContent" class="d-none">
                </div>
-->
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>

    <!-- Favorites layout (Last days; hidden by default) -->

    <div id="favoritesLayout" class="row g-0 flex-grow-1 h-100 d-none">
      <div class="col-12 h-100">
        <div class="content-wrapper h-100 overflow-auto d-flex flex-column">

          <!-- Header (duplicate of main; wired live to the same handlers) -->

          <header class="bg-light border-bottom py-2 px-2 text-break fs-5 d-flex align-items-center">
            <div class="d-flex align-items-center">

              <?php require( __DIR__ . '/app_logo.php'); ?>

              <select onchange="mainCrl.userSelectChange(event)" class="js-userSelect border-0 fs-5">
                <?php foreach( User::getAll() as $userId ): ?>
                  <option value="<?= $userId ?>"<?= self::iif( $userId == User::current('id'), ' selected') ?>>
                    <?= User::byId( $userId )->get('name') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <button onclick="mainCrl.switchDayBtnClick(event)"
              data-sel = "<?= $this->mode ?>"
              class    = "js-switchDayBtn btn ms-auto p-1 py-0 border"
            >
              <?= self::switch( $this->mode, [
                  'current' => $day,
                  'last'    => '-1 day',
                  'next'    => '+1 day'
                ]) ?? $day
              ?>
            </button>
          </header>

          <!-- Scrollable content area -->

          <div class="flex-grow-1 overflow-auto p-3">
            <!-- <div class="row g-3"> -->  <!-- TASK what is this ? -->

            <?php require( __DIR__ . '/main/last_days.php'); ?>

          </div>
        </div>
      </div>
    </div>

    <?php require( __DIR__ . '/chart.php'); ?>

  </main>

  <?php print $this->renderView( __DIR__ . '/modal/tips.php'); ?>  <!-- prefer new scope when much code -->
  <?php print $this->renderView( __DIR__ . '/modal/settings.php'); ?>
  <?php require( __DIR__ . '/modal/new_entry.php'); ?>
  <?php require( __DIR__ . '/modal/move_food.php'); ?>
  <?php require( __DIR__ . '/modal/info.php'); ?>
  <?php require( __DIR__ . '/modal/confirm.php'); ?>
  <?php require( __DIR__ . '/modal/search.php'); ?>

  <div id="errorPage" style="display: none;">
    <?php require( __DIR__ . '/error_page.php'); ?>
  </div>

  <?php if( config::get('devMode') && config::get('showDim') ): ?>
    <script>
      // position:fixed -> out of normal flow, doesn't push body past 100% and avoids a page scrollbar
      document.write('<p style="position:fixed; bottom:4px; left:8px; margin:0; font-size:0.75rem; opacity:0.6; pointer-events:none; z-index:1040;">(dev info: ' + window.innerWidth + ' x ' + window.innerHeight + 'px, dpr: ' + window.devicePixelRatio + ')</p>')
    </script>
  <?php endif; ?>

<!--
< ?php endif;  // error_page and login ?>
-->
<script src="lib/jquery-3.7.1.min.js"></script>
<script src="lib/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/jquery-ui-1.13.3/jquery-ui.min.js"></script>
<script src="lib/mermaid-11.3.0.js"></script>
<script src="lib/marked.min.js"></script>
<script src="lib/overlay_MOV.js"></script>
<script src="lib/sortable.js"></script>
<script src="lib/frm/events_240321.js"></script>
<script src="lib/frm/query_240321.js"></script>
<script src="lib/frm/query_data_240328.js"></script>
<script src="lib/frm/YAMLish_241016.js"></script>
<script src="lib/frm/send_240420.js"></script>
<!-- <script src="lib/frm/fade_230808.js"></script> -->
<script src="app.js?v=<?= time() ?>"></script>
<script src="MainController.js?v=<?= time() ?>"></script>
<script src="NutritionWidgetsController.js?v=<?= time() ?>"></script>
<script src="DropMenu.js?v=<?= time() ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="ChartsController.js?v=<?= time() ?>"></script>
<!-- <script src="SettingsController.js?v=<?= time() ?>"></script> -->
<script>

// ajax.file = 'ajax.php'

var dayEntries, mainCrl, widgetsCrl, chartsCrl, dropMenu

ready( function() {

  dayEntries = JSON.parse('<?= json_encode($this->dayEntries) ?>')

  mainCrl = new MainController()
  mainCrl.date = '<?= $this->date ?>'
  mainCrl.updSummary()

  widgetsCrl = new NutritionWidgetsController()
  chartsCrl  = new ChartsController()

  dropMenu = new DropMenu()   // handles every .drop-menu on the page

  setupTabletErrorHandler()
})

</script>
</body>
</html>
