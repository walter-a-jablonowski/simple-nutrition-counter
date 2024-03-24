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
    $identifier = $request['identifier'];
    $identifier = explode('/', $identifier);
    // array_shift($identifier);

    // if( count($identifier) == 1)
    // {
    //   if( ! method_exists($this, $identifier))    // methods like index() as done in laravel
    //     throw new \Exception('No method found');
    //      
    //   $this->$identifier();
    // }
    if( count($identifier) == 2 && method_exists($this, $identifier[1]))
    {
      $method = $identifier[1];
      $this->$method();
    }
    elseif( count($identifier) > 2 )
    {
      array_shift($identifier);
      $request['identifier'] = implode('/', $identifier);

      $this->subViewControllers[$identifier]->dispatch( $request );
    }

    throw new \Exception('No method found');
  }
}

?>
