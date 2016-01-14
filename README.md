# Simple URI router

```php
use Colorium\Routing\Router;

// setup routes
$router = new Router;
$router->add('GET /hello/:name', function($name)
{
    echo 'Hello', $name, ' !';
});

// search route
$route = $router->find('GET /hello/you');

// if route not found
if(!$route) {
    // 404
}

// route details
$route->method; // 'GET'
$route->uri; // '/hello/:you'
$route->resource; // function($name) { ... }
$route->params; // ['name' => 'you']
```

## Install

`composer require colorium/routing`