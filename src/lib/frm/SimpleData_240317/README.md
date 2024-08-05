# Simple data

Simple data container (intentionally basic function only)

this currently has some more functions that might be moved in Data


See also

- upper readme
- more function see Data class


Concept
----------------------------------------------------------

- features from SimpleDataClass and
- single value crud
  - has
  - maybe del mutiple keys
- replace placeholders
- arrays functions

- no alias feature
- no underscore => camel feature, use exactly what is in fil sys


Sample
----------------------------------------------------------

```php

$data = new SimpleData()
$data = new SimpleData( $array, ... )      // later overwr previous

$data->has('my.key')
$data->get('my.key')                       // replace like ˋ{@my.val}ˋ
$data->get('my.key') ?: $default
$data->my                                  // TASK: we could convert my.key in myKey
$data->require('my.key')

$data->set('my.key', $value )

// Array

$count = count( $data->get('my.key'))      // combine with PHP (less methods cause more simple)
$keys  = array_keys( $data->get('my.key')  // (removed methods for this even if more efficient)

$data->push('my.key', $value)
$data->push('my.key', ['myIdx' => $value])
```
