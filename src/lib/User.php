<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'lib/SimpleData_240317/SimpleData.php';


/*

*/
class User extends SimpleData  /*@*/
{
  // TASK: improve

  // Symfony: `UserRepository` with query like functions
  // Laravel: `User::where() User::all()`
  //           also use functions as wrapper for static `auth()->user()`
  // NET:     `UserManager`
  // Java:    `System.getProperty("user.name")` or similar

  // we try to keep code dependency less (no dependencies between classes and complex init in app)
  // a central point App::get() where things can be modified might be useful

  public static function getAll()
  {
    // TASK: remove fix fld

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
