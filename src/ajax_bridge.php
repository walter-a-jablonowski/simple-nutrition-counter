<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/SimpleData_240317/SimpleData.php';
require_once 'lib/ConfigStatic_240323/config.php';
require_once 'controller.php';


// Currently used as a bridge for dashbard integration

// $this->config = new SimpleData( Yaml::parse( file_get_contents('config.yml')));
config::setData( new SimpleData( Yaml::parse( file_get_contents('config.yml'))));

$args = json_decode( file_get_contents('php://input'), true);

$controller = new FoodsController();
echo $controller->dispatch($args);

?>
