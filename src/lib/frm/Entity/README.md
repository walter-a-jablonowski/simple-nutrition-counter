
Provides an interface to a list of entities in fil sys, e.g. users

Dependencies: Symfony YML, SimpleData


Design
----------------------------------------------------------

TASK: MOV We try to keep code dependency less (no dependencies between classes and complex init in app) a central point App::get() where things can be modified might be useful.

Frameworks use sth similar:

- Symfony: `UserRepository` with query like functions
- Laravel: `User::where() User::all()`
           also use functions as wrapper for static `auth()->user()`
- NET:     `UserManager`
- Java:    `System.getProperty("user.name")` or similar

Misc

- `User::getAll()` kind of fluent
- We use a singleton like approach over objects
- We call it Entity for now
- Addon for SimpleData (single responsible principle)


Sample
----------------------------------------------------------

```php

class User extends Entity
{
  public static function init( string $baseFld, string $current )
  {
    self::$baseFld = $baseFld;
    self::$current = $current;
  }
}

User::init('data/users', $_SESSION['userId'] );

$user = User::current();

$name = User::current( $id )->get('name');  // methods see SimpleData
$name = User::current('name');              // short

$subData = $user->sub('sub/fil')->get('some.key');

foreach( User::getAll() as $id )
{
  $user = User::byID( $id );
  print "$user->name\n";
}
```
