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
      return $this->render( $request );
    }
    elseif( count($identifier) == 1 && method_exists($this, $identifier[0]))
    {
      $method = $identifier[0];
      return $this->$method( $request );
    }
    elseif( count($identifier) > 1 )
    {
      return $this->subViewControllers[$identifier]->dispatch( $request );
    }
    else
    {
      throw new \Exception('No method found');
    }
  }
}

?>
