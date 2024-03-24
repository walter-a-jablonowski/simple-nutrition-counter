<?php

abstract class ControllerBase
{
  protected $model;
  protected $view;

  protected array $subViewControllers = [];

  public function __construct( $model = null, $view = null ) {
    $this->model = $model;
    $this->view  = $view;
  }

  public function registerSubViewController( $ident, $controller )
  {
    $this->subViewControllers[$ident] = $controller;
  }

  // currently using trait cause ajax methods must be able access base class data (if any)

  // public function registerPartialController( $ident, $controller )
  // {
  //   $this->partialControllers[$ident] = $controller;
  // }

  public function dispatch( $request )
  {
    $identifier = trim( $request['identifier']);
    $identifier = $identifier ? explode('/', $identifier) : [];

    if( ! count($identifier) )  // ins of methods like index() as done in laravel
    {
      $this->render();
    }
    elseif( count($identifier) == 1 && method_exists($this, $identifier[1]))
    {
      $method = $identifier[1];
      $this->$method();
    }
    elseif( count($identifier) > 1 )
    {
      $this->subViewControllers[$identifier]->dispatch( $request );
    }
    else
    {
      throw new \Exception('No method found');
    }
  }
}

?>
