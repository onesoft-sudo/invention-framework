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
}
