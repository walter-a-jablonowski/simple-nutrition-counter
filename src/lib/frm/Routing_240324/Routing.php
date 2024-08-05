<?php

class Router
{
  private $routes = [];

  public function register( $path, $callback)
  {
    $this->routes[] = [
      // 'method' => $method,
      'path'      => $path,
      'callback'  => $callback
    ];
  }

  public function run()
  {
    $requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    foreach( $this->routes as $route)
    {
      // if( $requestMethod == $route['method'] && preg_match($this->buildPattern($route['path']), $requestUri, $matches))
      if( preg_match( $this->buildPattern( $route['path']), $requestUri, $matches))
      {
        array_shift($matches); // remove the full match
        $pathParams  = array_combine( $this->extractPlaceholders($route['path']), $matches);
        $queryParams = $_GET;
        $bodyParams  = json_decode(file_get_contents('php://input'), true) ?: [];

        $params = array_merge($pathParams, $queryParams, $bodyParams);
        call_user_func($route['callback'], $params);
        return;
      }
    }

    // No route matched
    http_response_code(404);
    echo '404 Not Found';
  }

  private function buildPattern($path)
  {
    return '@^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path) . '$@';
  }

  private function extractPlaceholders($path)
  {
    preg_match_all('/\{(\w+)\}/', $path, $matches);
    return $matches[1];
  }
}
