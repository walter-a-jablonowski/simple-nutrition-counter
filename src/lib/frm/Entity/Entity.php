<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;


/*


*/
class Entity extends SimpleData  /*@*/
{
  protected string $baseFld;
  protected string $current;

  public static function getAll() : array
  {
    return array_filter( scandir( self::$baseFld ),
      fn($fil) => is_dir( self::$baseFld . "/$fil") && ! in_array( $fil, ['.', '..'])
    );
  }

  public static function byId( string $entityId ) : SimpleData
  {
    // return new SimpleData( array_merge(
    return new self( array_merge(
      ['id' => $entityId],
      Yaml::parse( file_get_contents( self::$baseFld . "/$entityId/-this.yml"))
    ));
  }

  public static function current( ?string $key = null )
  {
    $entity = self::byId( self::$current );

    if( is_null($key))
      return $entity;
    else
      return $entity->get($key);
  }


  // TASK: method for getting sub fils

  public function sub( string $key ) : SimpleData
  {
    return new SimpleData(
      Yaml::parse( file_get_contents( self::$baseFld . "/$key.yml"))
    );
  }
}

?>
