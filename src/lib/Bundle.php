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
    if( [$file, $id, $key] = self::getFile( $key ))
    {
      $data = SimpleData( array_merge(
        ['id' => $id],
        Yaml::parse( file_get_contents("data/bundles/$file.yml"))
      ));

      return $data->get( $key );
    }
    else
    {
      return parent::get( $key );
    }
  }

  public function getFile( string $key )
  {
    $keys = explode('.', $key);
    $file = null; $id = null; $key = null;
    $fil  = '';

    while( $key = array_shift($keys))  // TASK
    {
      $fil .= "/$key";
      
      if( file_exists("data/bundles/$fil.yml"))
      {
        $file = "data/bundles/$fil";
        $id   = $fil;
        $key  = implode('.', $keys);
        break;
      }
      elseif( file_exists("data/bundles/$fil/-this.yml"))
      {
        $file = "data/bundles/$fil/-this.yml";
        $id   = $fil;
        $key  = implode('.', $keys);
        break;
      }
    }

    return [$file, $id, $key];
  }
}

?>
