<?php

/*

Standalone tests for the signal colors of the quick summary widgets (green inside the
range, red above it, see MainController.#updWidgetSignals).
Run from the `src` directory:  php tools/test_widget_signal_colors.php

The colors are plain css classes on the widget, so the trap is bootstrap: its
background utilities (bg-white and friends) are declared with !important and win
against any rule of ours, no matter how specific. A widget must therefore get its
neutral background from the stylesheet, never from a bg- utility in the markup

*/

chdir( dirname(__DIR__));

$pass = 0;
$fail = 0;

function check( string $name, bool $ok, string $detail = '')
{
  global $pass, $fail;
  if( $ok ) { $pass++; echo "  PASS  $name\n"; }
  else      { $fail++; echo "  FAIL  $name" . ($detail ? "  ($detail)" : '') . "\n"; }
}

$view = file_get_contents('view/main/edit/quick_summary.php');
$css  = file_get_contents('style/app.css');

// 1) No widget may carry a bootstrap background utility (commented out markup counts,
//    it is the template for the next widget)

preg_match_all('/class\s*=\s*"([^"]*\bnutrition-widget\b[^"]*)"/', $view, $matches);

check('widgets found in the view', count( $matches[1]) > 1, count( $matches[1]) . ' widgets');

foreach( $matches[1] as $classes )
{
  preg_match('/\bbg-[a-z0-9-]+/', $classes, $utility);

  check('no bg- utility on ' . trim( preg_replace('/\s+/', ' ', $classes)),
        ! $utility, $utility ? "has $utility[0], it is !important and kills the signal color" : '');
}

// 2) The neutral background comes from the stylesheet instead

check('widget has a background in the css',
      (bool) preg_match('/\.nutrition-widget\s*\{[^}]*background-color\s*:/', $css));

// 3) The signal colors themselves are still there

foreach(['widget-red', 'widget-green', 'widget-yellow'] as $signal )
  check("$signal sets a background",
        (bool) preg_match('/\.' . $signal . '\s*\{[^}]*background-color\s*:/', $css));

// 4) Bootstrap really does use !important, which is why rule 1 exists

$bootstrap = file_get_contents('lib/bootstrap-5.3.3-dist/css/bootstrap.min.css');

check('bootstrap bg-white is !important',
      (bool) preg_match('/\.bg-white\{[^}]*!important\}/', $bootstrap));

echo "\n  $pass passed, $fail failed\n";

exit( $fail ? 1 : 0);

?>
