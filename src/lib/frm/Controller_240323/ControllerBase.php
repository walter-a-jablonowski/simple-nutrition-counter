<?php

abstract class ControllerBase
{
  protected array $subViewControllers = [];

  public function __construct() {

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


  // TASK: DEPRECATED use functions.php and standard/dev

  // same as inc() be able use class context via $this

  /*@

  inc

  - must be non static

  **included fil**

  ```php
  extract($args);

  $return['done'] = [];
  ```

  */
  public function renderView( string $INC_VIEW, $args = null, &$return = null ) /*@*/
  {
    ob_start();                   // alternative: $s = require()
    require($INC_VIEW);
    $INC_STR_R = ob_get_clean();  // var has unusual name

    return $INC_STR_R;
  }


  // from damn-small-engine (append() simplified)

  /*@

  Append a string

  like print() adds space before

  */
  public static function append( $s ) /*@*/
  {
    // $s = self::print( $arg1, $arg2, $arg3 );

    if( $s )  return " $s";
    else      return '';
  }

  // from standard/dev

  /*@

  iif

  - valid are non-false and 0, 0.0, "0"

  */
  public static function iif( $if, $true, $false = '' ) /*@*/
  {
    if( $if || $if === 0 || $if === 0.0 || $if === "0" )
      return $true;
    else
      return $false;
  }

  /*@

  - doesn't work for bool or float keys

  ```php

  <?= self::iif( $price && $expensive, self::switch( settings::get('currency'), [
    'EUR' => '<i class="bi bi-currency-euro small text-secondary"></i>',
    'USD' => '<i class="bi bi-currency-dollar small text-secondary"></i>'
  ])) ?>
  ```

  */
  public static function switch( $value, $arr ) /*@*/
  {
    if( isset( $arr[$value]))  // &&  ($arr[$or] || $arr[$or] === 0 || $arr[$or] === 0.0 || $arr[$or] === "0"))
      return $arr[$value];
    else
      // return $default;
      return null;
  }
}

?>
