# Controller

AI generated (unfinished)


Usage
----------------------------------------------------------

### Page load

```
my-domain.com/myPage
my-domain.com?identifier=myPage

=> routing removes page, controller gets empty identifier => default = render()
```


### Ajax

```javascript

//    v also can be controller.php if no routing

send('index.php', { identifier: 'myPage/subView/save', data: ... ]}, function( result, data ) {

})

// => routing removes page, controller gets identifier = subView/save
```


### Controller

```php

class SampleController extends ControllerBase
{
  use SomeAjaxController;  // trait (partial class if long)

  // public function __constuct()
  public function __constuct( $model = null, $view = null )
  {
    // parent::__construct();  // or
    parent::__construct( $model, $view );

    // Register sub and ajax controllers
    
    // $this->registerSubViewController('myController', new MyController());
    // ...
  }

  public function render( $request ) {
    $data = $this->model-> ...;
    $data = $this->view-> ...;   // use some Engine ...
  }

  // dispatch() may be used by Routing just like any method here (which handles sub controllers)

  public function getData( $request )
  {
    // ajax ...
  }

  public function miscHelper( ... )
  {

  }
}
```


Dev
----------------------------------------------------------

### Removed, we use just render() instead

```php

// public function index( $request ) {
//   $data = $this->model-> ...;
//   $data = $this->view-> ...;   // use some Engine ...
// }

// public function someStaticPage( ) {
// 
// }
```
