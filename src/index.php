<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/ConfigStatic_240323/config.php';
// require_once 'lib/Routing_240324/Routing.php';
require_once 'controller.php';


config::instance( new SimpleData( Yaml::parse( file_get_contents('config.yml'))));

// Current simple solution #code/routing

$isAjax = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if( ! $isAjax )
{
  // Routing would be sth like (delayed due to effort, see tasks, we currently make features)

  // $router = new Router();
  //
  // $router->register('', function($args) {             // empty should match anything (no apache config)
  //
  //   $identifier = explode('/', $args['identifier']);  // remove first key is managed by routing
  //   array_shift($identifier);
  //   $args['identifier'] = implode('/', $identifier);
  //
  //   $controller = new FoodsController();
  //   echo $controller->dispatch($args);
  // });
  //
  // $router->run();

  $controller = new FoodsController();
  echo $controller->render();
}
else
{
  $args = json_decode( file_get_contents('php://input'), true);

  $controller = new FoodsController();
  echo $controller->dispatch($args);
}

?>
