<?php

namespace Colorium\Routing;

use Colorium\Routing\Contract\RouterInterface;
use Colorium\Runtime\Annotation;

/**
 * Uri-based router
 *
 * Vocabulary :
 * - method : http verb (ANY, GET, POST, PUT, DELETE, OPTIONS, HEAD)
 * - uri : relative url (/errors)
 * - query : method + uri (GET /errors)
 */
class Router implements Contract\RouterInterface
{

    /** @var Route[] */
    protected $routes = [];


    /**
     * Setup router
     * 
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        foreach($routes as $query => $resource) {
            $this->add($query, $resource);
        }
    }


    /**
     * Add route definition
     *
     * @param string $query
     * @param $resource
     * @param array $meta
     * @return $this
     */
    public function add($query, $resource, array $meta = [])
    {
        $query = static::clean($query);
        list($method, $uri) = explode(' ', $query);

        $this->routes[$query] = new Route($method, $uri, $resource, $meta);

        return $this;
    }


    /**
     * Parse class methods annotation and add route
     *
     * @param string $class
     * @param array $meta
     * @return $this
     */
    public function parse($class, array $meta = [])
    {
        $methods = get_class_methods($class);
        foreach($methods as $method) {
            $query = Annotation::ofMethod($class, $method, 'uri');
            if($query) {
                $this->add($query, [$class, $method], $meta);
            }
        }

        return $this;
    }


    /**
     * Mount router under prefix query
     *
     * @param $prefix
     * @param RouterInterface $router
     * @param array $meta
     * @return $this
     */
    public function mount($prefix, RouterInterface $router, array $meta = [])
    {
        $prefix = '/' . trim($prefix, '/');
        foreach($router->routes() as $route) {
            $route->uri = $prefix . $route->uri;
            $route->meta = array_merge($route->meta, $meta);
            $query = static::clean($route->method . ' ' . $route->uri);
            $this->routes[$query] = $route;
        }

        return $this;
    }


    /**
     * Get all routes
     *
     * @return Route[]
     */
    public function routes()
    {
        return $this->routes;
    }


    /**
     * Find route
     * 
     * @param string $query
     * @return Route
     */
    public function find($query)
    {
        // parse query
        $query = static::clean($query);
        list($method, $uri) = explode(' ', $query);

        // search in all routes
        foreach($this->routes as $route) {
            $route->compiled = static::compile($route->uri);
            if(preg_match($route->compiled, $uri, $params) and ($route->method == 'ANY' or $method == $route->method)) {
                $route->params = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);
                return $route;
            }
        }
    }


    /**
     * Reverse finding by resource
     *
     * @param $resource
     * @param array $params
     * @return Route
     */
    public function reverse($resource, array $params = [])
    {
        // search in all routes
        if($key = array_search($resource, $this->routes)) {
            $route = $this->routes[$key];
            $route->params = $params;
            return $route;
        }
    }


    /**
     * Clean query
     *
     * @param string $query
     * @param array $params
     * @return string
     */
    protected static function clean($query, array $params = [])
    {
        $method = 'ANY';
        $uri = '/';

        if(preg_match('#^(ANY|GET|POST|PUT|DELETE|OPTIONS|HEAD) (.+)$#i', $query, $out)) {
            $method = $out[1];
            $uri = $out[2];
        }

        $method = trim(strtoupper($method));
        $uri = '/' . trim($uri, '/ ');
        foreach($params as $key => $value) {
            $uri = str_replace(':' . $key, $value, $query);
        }

        return $method . ' ' . $uri;
    }


    /**
     * Compile query into regex
     *
     * @param string $uri
     * @return string
     */
    protected static function compile($uri)
    {
        $regex = str_replace('#', '#', $uri);
        $regex = preg_replace('#\:([a-zA-Z0-9_]+)#', '(?P<$1>[^/]+)', $regex);
        return '#^' . $regex . '$#';
    }

}