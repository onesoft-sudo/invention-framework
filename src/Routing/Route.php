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
use OSN\Framework\Core\Middleware;

/**
 * Class Route
 *
 * @package OSN\Framework\Routing
 * @author Ar Rakin <rakinar2@gmail.com>
 * @todo Add group() method
 */
class Route
{
    /**
     * The unique name of the route.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * The request URI of the route.
     *
     * @var string
     */
    protected string $path;

    /**
     * The method that the route corresponds to.
     *
     * @var string
     */
    protected string $method;

    /**
     * Middleware array that will be applied to this route.
     *
     * @var Middleware[]
     */
    protected array $middleware = [];

    /**
     * The action that will be executed for this route.
     *
     * @var mixed
     */
    protected $action;

    /**
     * The dynamic parameters for the route.
     *
     * @var array
     */
    protected array $params = [];

    /**
     * Route constructor.
     *
     * @param string $method
     * @param string $path
     * @param array|string|Closure $action
     * @param string|null $name
     * @param array $middleware
     */
    public function __construct(string $method, string $path, $action, string $name = null, array $middleware = [])
    {
        $this->method($method);
        $this->path($path);
        $this->name($name);
        $this->middleware($middleware);
        $this->action($action);
    }

    /**
     * Get or set the method of this route.
     *
     * @param null $method
     * @return $this|string
     */
    public function method($method = null)
    {
        if ($method != null) {
            $this->method = $method;
            return $this;
        }

        return $this->method;
    }

    /**
     * Get or Set the action callback for this route.
     *
     * @param null $action
     * @return Closure|Route|string[]
     */
    public function action($action = null)
    {
        if ($action != null) {
            $this->action = $action;
            return $this;
        }

        return $this->action;
    }

    /**
     * Get or set the path (Request URI) of this route.
     *
     * @param null $path
     * @return $this|string
     */
    public function path($path = null)
    {
        if ($path != null) {
            $this->path = $path;
            return $this;
        }

        return $this->path;
    }

    /**
     * Get or set the name of the route. The name must be unique.
     *
     * @param null $name
     * @return $this|string|null
     */
    public function name($name = null)
    {
        if ($name != null) {
            $this->name = $name;
            return $this;
        }

        return $this->name;
    }

    /**
     * Get or set the middleware for this route.
     *
     * @param null $middleware
     * @return $this|Middleware[]
     */
    public function middleware($middleware = null)
    {
        if ($middleware != null) {
            if (is_array($middleware)) {
                $this->middleware = array_merge($this->middleware, array_map(fn($m) => is_string($m) ? app()->createNewObject($m) : $m, $middleware));
            }
            else
                $this->middleware[] = is_string($middleware) ? app()->createNewObject($middleware) : $middleware;

            return $this;
        }

        return $this->middleware;
    }

    public function matches(string $string)
    {
        $bool = preg_match_all("/^" . addcslashes($this->path, '/$^') . "$/i", $string, $this->params);
        array_shift($this->params);

        $this->params = array_map(function($value) {
            return $value[0] ?? null;
        }, $this->params);

        return (bool) $bool;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function __toString()
    {
        return $this->path();
    }
}
