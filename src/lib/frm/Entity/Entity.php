<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;


/*


- extends SimpleData cause of overriding get()

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

  public function get( string $key )
  {
    // TASK: maybe compare this with older impl json db

    [$file, $id, $key] = self::getFile( $key );

    if( $file )
    // if( [$file, $id, $key] = self::getFile( $key ))  // TASK: possible?
    {
      $data = new SimpleData(
        Yaml::parse( file_get_contents( self::$baseFld . "/$file.yml"))
      );

      if( ! $key )
        return $data;
      else
        return $data->get( $key );
    }
    else
    {
      return parent::get( $key );
    }
  }

  private function getFile( string $key ) : array
  {
    $full = $key;
    $keys = explode('.', $key);
    $file = null; $id = null; $key = null;
    $fil  = '';

    while( $sub = array_shift($keys))
    {
      $fil .= "/$sub";
      
      if( file_exists( self::$baseFld . "/$fil.yml"))
      {
        $file = "/$fil.yml";
        $id   = $fil;
        $key  = implode('.', $keys);
        break;
      }
      elseif( file_exists( self::$baseFld . "/$fil/-this.yml"))
      {
        $file = "/$fil/-this.yml";
        $id   = $fil;
        $key  = implode('.', $keys);
        break;
      }
    }

    return [$file, $id, $key];
  }
}

?>
