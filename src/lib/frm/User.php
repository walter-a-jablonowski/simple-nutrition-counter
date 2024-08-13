<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

// require_once 'lib/frm/Entity/Entity.php';
require_once 'lib/frm/SimpleData_240317/SimpleData.php';


/*

*/
class User extends SimpleData  /*@*/
// class User extends Entity
{

  public static function getAll()
  {
    return array_filter( scandir('data/users'),
      fn($fil) => is_dir("data/users/$fil") && ! in_array( $fil, ['.', '..'])
    );
  }

  public static function byId( string $userId )
  {
    return new SimpleData( array_merge(
      ['id' => $userId],
      Yaml::parse( file_get_contents("data/users/$userId/-this.yml"))
    ));
  }

  public static function current( ?string $key = null )
  {
    $user = self::byId( $_SESSION['userId'] );

    if( is_null($key))
      return $user;
    else
      return $user->get($key);
  }
}

?>
