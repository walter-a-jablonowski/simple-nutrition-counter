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


  public function require( string $key )
  {
     $r = $this->get( $key );

     if( is_null($r) )
       throw new \Exception("$key missing");

     return $r;
  }


  public function get( string $key )
  {
    $r = $this->findKey( $key, $make = false );

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
    $elem = &$this->findKey( $key, $make = true );
    $elem = $value;
  }


  public function __set( string $key, $value ) : void
  {
    $this->data[$key] = $value;
  }


  // Array

  public function push( string $key, $value )
  {
    $keys = explode('.', $key);
    $sub  = array_pop($keys);
    $key  = implode('.', $keys);
    
    $elem = &$this->findKey( $key, $make = true );
    $elem[$sub] = $value;
  }


  // Helper

  private function &findKey( string $key, bool $make = true )
  {
    // TASK: keep in this class (reduce dependencies) or move

    $keys = explode('.', $key);
    $r = &$this->data;

    foreach( $keys as $key )
    {
      if( ! isset( $r[$key]) && $make)
        $r[$key] = null;
      elseif( ! isset( $r[$key]) && ! $make)
        return null;

      $r = &$r[$key];
    }

    return $r;
  }
}

?>
