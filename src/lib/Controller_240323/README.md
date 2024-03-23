# Controller

AI generated (unfinished)

```php

class SampleController extends ControllerBase
{
  public function __constuct( $model = null, $view = null )
  {
    parent::__construct( $model, $view );

    // Register sub and ajax controllers
    
    // $this->registerSubViewController('myController', new MyController());
    // ...
  }

  // Requests may be routet -> dispatch which handles sub controllers

  // public function index( $request ) {
  //   $data = $this->model-> ...;
  //   $data = $this->view-> ...;   // use some Engine ...
  // }

  // public function getData( $request )
  // {
  //   // ajax ...
  // }
}
```

Routing should extract args

```php

$isAjax = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if( method_exists($this, $action))
  $this->$action();

if ($isAjax) {
  return json_decode(file_get_contents('php://input'), true) ?: [];
}
else {
  $requestUri = $_SERVER['REQUEST_URI'];
  $path       = parse_url($requestUri, PHP_URL_PATH);
  $segments   = explode('/', trim($path, '/'));
  return [
    'identifier' => $segments[0] ?? null,
    'sub' => $segments[1] ?? null,
  ];
}
```
