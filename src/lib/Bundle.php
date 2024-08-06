<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/frm/SimpleData_240317/SimpleData.php';


/*

- can get -this of a bundle as SimpleData
- can also deliver SimpleData for sub files

*/
class Bundle extends SimpleData  /*@*/
{
  protected SimpleData $current;

  // we try to keep code dependency less (no dependencies between classes and complex init in app)
  // a central point App::get() where things can be modified might be useful

  public static function init( ?SimpleData $current )
  {
    self::$current = self::byId( $current );
  }

  public static function getAll()
  {
    // TASK: remove fix fld?

    return array_filter( scandir('data/bundles'),
      fn($fil) => is_dir("data/bundles/$fil") && ! in_array( $fil, ['.', '..'])
    );
  }

  public static function byId( string $id )
  {
    return new SimpleData( array_merge(
      ['id' => $id],
      Yaml::parse( file_get_contents("data/bundles/$id/-this.yml"))
    ));
  }

  public static function current( ?string $key = null )
  {
    if( is_null($key))
      return self::$current;
    else
      return self::$current->get($key);
  }

  public function get( string $key )
  {
    if( strpos( $key, '/') !== false )  // TASK: improve?
    {                                   // TASK: -this
      $id = array_key_last( explode('/', $key));
      
      return SimpleData( array_merge(
        ['id' => $id],
        Yaml::parse( file_get_contents("data/bundles/$key.yml"))
      ));
    }
    else
    {
      parent::get( $key );
    }
  }
}

?>
