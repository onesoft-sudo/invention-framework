<?php


namespace OSN\Framework\Http;


use Closure;
use OSN\Framework\Routing\Route;

trait HTTPMethodRouterHelper
{
    public function addRoute(string $method, string $route, $callback): Route
    {
        $routeObject = new Route($method, $route, $callback);
        $this->routes[] = $routeObject;
        return $routeObject;
    }

    public function get(string $route, $callback)
    {
        return $this->addRoute("GET", $route, $callback);
    }

    public function post(string $route, $callback)
    {
        return $this->addRoute("POST", $route, $callback);
    }

    public function put(string $route, $callback)
    {
        return $this->addRoute("PUT", $route, $callback);
    }

    public function patch(string $route, $callback)
    {
        return $this->addRoute("PATCH", $route, $callback);
    }

    public function delete(string $route, $callback)
    {
        return $this->addRoute("DELETE", $route, $callback);
    }

    /**
     * @param string $givenPath
     * @param string $givenMethod
     * @return false|Route
     */
    public function findRoute(string $givenPath, string $givenMethod = '')
    {
        foreach ($this->routes as $route) {
            if ($route->path() === $givenPath || $route->matches($givenPath)) {
                if ($givenMethod === '') {
                    return $route;
                }
                else {
                    if ($route->method() == $givenMethod)
                        return $route;
                }
            }
        }

        return false;
    }

    public function findByLogic(Closure $closure)
    {
        foreach ($this->routes as $route) {
            if (call_user_func_array($closure, [$route]) === true) {
                return $route;
            }
        }

        return false;
    }
}
