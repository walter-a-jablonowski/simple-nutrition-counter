<?php

// page wrapper for standalone use

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/ConfigStatic_240323/config.php';
require_once 'controller.php';

?><!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Nutrition counter</title>

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> -->
  <link rel="stylesheet" href="style/bootstrap_themed.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="style/app.css" rel="stylesheet">
</head>
<body>

  <nav class="navbar navbar-expand-lg bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand text-white" href="#">Nutrition counter</a>
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
  <script src="lib/events_240321.js"></script>
  <script src="lib/query_240321.js"></script>
  <script src="lib/send_230808.js"></script>
  <script src="lib/fade_230808.js"></script>

  <div class="container-fluid mt-3">

    <?php
    
      // $this->config = new SimpleData( Yaml::parse( file_get_contents('config.yml')));
      config::setData( new SimpleData( Yaml::parse( file_get_contents('config.yml'))));

      $controller = new FoodsController();
      echo $controller->view();

    ?>

  </div>

  <a href="https://bootstrap.build/license">BS theme by bootstrap.build</a>

</body>
</html>
