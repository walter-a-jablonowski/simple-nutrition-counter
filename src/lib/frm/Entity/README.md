
Provides an interface to a list of entities in fil sys, e.g. users

Dependencies: Symfony YML, SimpleData


Design
----------------------------------------------------------

see frm readme > Design !

```php

// Simple: load data only, save in a single file only
// TASK: maybe compare this with older impl json db

$entity = MyEntities::byId('my.id');  // manager class (single responsible principle)
$entity = new MyEntity( $data );      // may be from cache
// or
$users  = CachedEntities::init( User::class, $args );  // User extends SimpleData, args for construct

// Advanced

$entity->parent();
$entity->siblings();
// ...
$entity->nav('my.link');
// preprocess? define in code, resolve implicit links like 'amino: AMOUNT' to
// amino:
//   @link:
//   amount:

// filter and query functions ...
```


Sample
----------------------------------------------------------

```php

class User extends Entity
{
  // ...
}

User::init('data/users', $_SESSION['userId'] );

$user = User::current();

$name = User::current( $id )->get('name');
$name = User::current('name');      // short

$data = $user->get('sub.key');      // returns value of nested subkey in data/users/USER/-this.yml
$data = $user->get('sub.fil');      // returns a SimpleData with all data from data/users/USER/sub/fil.yml
$data = $user->get('sub.fil.key');  // returns alue of subkey "key" in data/users/USER/sub/fil.yml

// all methods see SimpleData class

foreach( User::getAll() as $id )
{
  $user = User::byID( $id );
  print "$user->name\n";
}
```
