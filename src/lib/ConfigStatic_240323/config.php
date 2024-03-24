<?php

class config
{
  private static SimpleData $config;

  public static function instance( ?SimpleData $config = null )
  {
    if( $config )
      self::$config = $config;
  
    return self::$config;
  }
  
  // Some of the methods as static

  public static function require( string $key )
  {
    return self::$config->require( $key );
  }

  public static function get( string $key )
  {
    return self::$config->get( $key );
  }

  public static function push( string $key, $value )
  {
    self::$config->push( $key, $value );
  }
}

?>
