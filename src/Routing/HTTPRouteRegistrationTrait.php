<?php
/*
 * Copyright 2020-2022 OSN Software Foundation, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OSN\Framework\Routing;


use Closure;

trait HTTPRouteRegistrationTrait
{
    /**
     * @var Route[] $routes
     */
    protected array $routes = [];

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
