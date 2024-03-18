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
- replace placeholders
- arrays functions


Sample
----------------------------------------------------------

```php

$data = new SimpleData()
$data = new SimpleData( $array, ... )  // later overwr previous

// Single value

$data->get('my.key')                   // replace like ˋ{@my.val}ˋ
$data->get('my.key') ?: $default
$data->my                              // TASK: we could convert my.key in myKey
$data->require('my.key')

$data->set('my.key', $value )

// Array

$data->push('my.key', $value)
$data->push('my.key', ['myIdx' => $value])
```
