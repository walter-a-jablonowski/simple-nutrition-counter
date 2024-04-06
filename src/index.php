<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/ConfigStatic_240323/config.php';
// require_once 'lib/Routing_240324/Routing.php';
require_once 'controller.php';


// Current simple solution #code/routing

// $isAjax = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
//
// if( $isAjax )
// {
//   // ...
// }

// Routing would be sth like (delayed due to effort, see tasks, we currently make more features)

// $router = new Router();
//
// $router->register('', function($args) {  // empty should match anything (no apache config)
//
//   $identifier = explode('/', $args['identifier']);  // remove first key is managed by routing
//   array_shift($identifier);
//   $args['identifier'] = implode('/', $identifier);
//
//   $controller = new FoodsController();
//   echo $controller->dispatch($params);
// });
//
// $router->run();


// Page wrapper for standalone use

?><!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Nutrition counter</title>

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> -->
  <link rel="stylesheet" href="style/bootstrap_themed.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  <link href="style/app.css" rel="stylesheet">

</head>
<body style="max-width: 800px;">

  <nav class="navbar navbar-expand-lg bg-primary">
    <div class="container-fluid">
      <div class="d-flex w-100 justify-content-between">
        <a class="navbar-brand text-white" href="#">Nutrition counter</a>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <script src="lib/events_240321.js"></script>
  <script src="lib/query_240321.js"></script>
  <script src="lib/lib/query_data_240328.js"></script>
  <script src="lib/send_230808.js"></script>
  <script src="lib/fade_230808.js"></script>

  <div class="container-fluid mt-3">

    <?php
    
      config::instance( new SimpleData( Yaml::parse( file_get_contents('config.yml'))));

      $controller = new FoodsController();
      echo $controller->render();

    ?>

  </div>

</body>
</html>
