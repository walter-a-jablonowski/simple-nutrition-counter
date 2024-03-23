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
    array_shift($identifier);
    $identifier = explode('/', $identifier);

    if( count($identifier) == 1)
    {
      if( ! method_exists($this, $identifier))
        throw new \Exception('No method found');

      $this->$identifier();
    }
    elseif( count($identifier) > 1 )
    {
      $request['identifier'] = implode('/', $identifier);

      $this->subViewControllers[$identifier]->dispatch( $request );
    }

    throw new \Exception('No method found');
  }
}

?>
