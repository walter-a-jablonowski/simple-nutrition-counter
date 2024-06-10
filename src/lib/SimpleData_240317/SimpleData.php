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


  public function require( string $key )
  {
     $r = $this->get( $key );

     if( is_null($r) )
       throw new \Exception("$key missing");

     return $r;
  }


  public function get( string $key )
  {
    // $r = $this->findKey( $key, $make = false );
    $keys = explode('.', $key);  // has own impl cause we can't retrun null from a findKey() function when byref
    $r = &$this->data;

    foreach( $keys as $key )
    {
      if( ! isset( $r[$key]))
        return null;

      $r = &$r[$key];
    }

    // Replace feature

    if( is_string($r) && false !== strpos( $r, '@'))
    {
      $vkeys = preg_match_all('/(?<=^|\s|\{)\@([A-Za-z0-9.]+)/', $r, $f) !== false ? $f[1] : [];
      
      // var_dump($keys);
      
      foreach( $vkeys as $vkey )
      {
        $r = str_replace("{@$vkey}", $this->get($vkey), $r);
        $r = str_replace("@$vkey",   $this->get($vkey), $r);
      }
    }

    $return = $r;  // remove the reference return byval
    return $return;
  }


  public function __get( string $key )
  {
    return $this->data[$key];
  }


  public function set( string $key, $value ) : void
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
    $elem[$sub][] = $value;
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
