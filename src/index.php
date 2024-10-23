<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';
require_once 'lib/frm/ConfigStatic_240323/config.php';
require_once 'lib/settings.php';
// require_once 'lib/frm/Routing_240324/Routing.php';
require_once 'lib/frm/Controller_240323/ControllerBase.php';
require_once 'controller/AppController.php';


// TASK: maybe use SimpleData inside cause static methods depend on the class

config::instance(   new SimpleData( Yaml::parse( file_get_contents('config.yml'))));
settings::instance( new SimpleData( config::get('defaultSettings')));  // TASK: (advanced) merge user settings

session_start();

// User (currently less important)
// just get it from session, currently no User obj

$user = $_SESSION['userId'] ?? config::get('defaultUser');  // v fallback in case user was renamed (no need for deleting the cookie)
$user = in_array( $user, User::getAll())  ?  $user  :  config::get('defaultUser');
$_SESSION['userId'] = $user;

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
  //   $controller = new AppController();
  //   echo $controller->dispatch($args);
  // });
  //
  // $router->run();

  if( config::get('devMode') && config::get('tryDesign'))
  {
    $controller = new class extends ControllerBase {
      public function render()
      {
        // ob_start();
        // require 'view/main/edit/food_info_design.php';
        // return ob_get_clean();
      }
    };
  }
  else
    $controller = new AppController();

  echo $controller->render();
}
else
{
  $args = json_decode( file_get_contents('php://input'), true);

  $controller = new AppController();
  echo json_encode( $controller->dispatch($args));
}

?>
