<?php

/*@

Simple data class

*/
class SimpleData /*@*/
{
  
  private array $data = [];


  public function __construct( ...$data )
  {
    foreach( $data as $d )
      $this->data = array_merge_recursive( $this->data, $d );
  }


  public function has( string $key ) : bool
  {
    return ! is_null( $this->get( $key ));
  }


  public function count( ?string $key )
  {
    if( is_null($key))
      return ! $this->data ? 0 : count($this->data);
    else
      return ! $this->has( $key ) ? 0 : count( $this->get($key) );
  }

  /*@

  Be able use in for loops like

  ```
  <?php foreach( $this->lastDaysView->all() as $day => $sums): ?>
  ```
  */
  public function all()  /*@*/
  {
    return $this->data;
  }


  public function keys( ?string $key = null ) : array
  {
    if( is_null($key))
      return ! $this->data ? [] : array_keys($this->data);
    else
      // TASK:
      // if( ! is_array($elem))
      //   throw new \Exception("$key is no array");
      return array_keys( $this->get($key) );
  }


  public function get( string $key )
  {
    // $r = $this->findKey( $key, $make = false );
    $keys = explode('.', $key);  // has own impl cause we can't return null from a findKey() function when byref
    $r = &$this->data;

    foreach( $keys as $key )
    {
      if( ! isset( $r[$key]))
        return null;

      $r = &$r[$key];
    }

    $return = $r;  // remove the reference byval  // TASK: verify really is byval

    // Replace feature

    if( is_string($return) && false !== strpos( $r, '@'))
    {
      $vkeys = preg_match_all('/(?<=^|\s|\{)\@([A-Za-z0-9.]+)/', $return, $f) !== false ? $f[1] : [];
      
      foreach( $vkeys as $vkey )
      {
        $return = str_replace("{@$vkey}", $this->get($vkey), $return);
        $return = str_replace("@$vkey",   $this->get($vkey), $return);
      }
    }

    return $return;
  }


  public function __get( string $key )
  {
    return $this->data[$key];
  }


  public function require( string $key )
  {
     $r = $this->get( $key );

     if( is_null($r) )
       throw new \Exception("$key missing");

     return $r;
  }


  public function set( ?string $key, $value ) : void  // TASK: why nullable?
  {
    $elem = &$this->findKey( $key );
    $elem = $value;
  }


  public function __set( string $key, $value ) : void
  {
    $this->data[$key] = $value;
  }


  // Array

  /*@
  
  Index based arrays only (use set for key)

  */
  public function push( string $key, $value )  /*@*/
  {
    $elem = &$this->findKey( $key );
    $elem = $value;
  }


  // Helper

  /*@
  
  we can't use make missing key return null cause error returning null when byref

  */
  private function &findKey( string $key/*, bool $make = true*/)  /*@*/
  {
    // TASK: keep in this class (reduce dependencies) or move

    $keys = explode('.', $key);
    $r = &$this->data;

    foreach( $keys as $key )
    {
      if( ! isset( $r[$key])/*&& $make*/)
        $r[$key] = null;
      // elseif( ! isset( $r[$key]) && ! $make)
      //   return null;

      $r = &$r[$key];
    }

    return $r;
  }
}

?>
