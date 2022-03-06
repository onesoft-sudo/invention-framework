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

namespace OSN\Framework\Core;


use OSN\Framework\View\Layout;
use OSN\Framework\View\View;

abstract class Controller
{
    /**
     * @var Middleware[] $middlewares
     */
    protected array $middlewares = [];
    protected array $middlewareMethods = [];

    public function render($view, array $data = [], $layout = ''): View
    {
        if ($view instanceof View) {
            return $view;
        }

        return new View($view, $data, $layout);
    }

    protected function setLayout($layout)
    {
        if ($layout instanceof Layout) {
            $name = $layout->getName();
        }
        else {
            $name = $layout;
        }

        App::$app->config["layout"] = str_replace('.', '/', $name);
    }

    /**
     * @param Middleware[]|string[] $middlewares
     */
    protected function setMiddleware(array $middlewares, array $methods = [])
    {
        if (is_string($middlewares[0])) {
            foreach ($middlewares as $key => $middleware) {
                $middlewares[$key] = is_string($middleware) ? new $middleware() : $middleware;
            }
        }

        $this->middlewares = array_merge($this->middlewares, $middlewares);
        $this->middlewareMethods = $methods;
    }

    public function getMiddlewareMethods(): array
    {
        return $this->middlewareMethods;
    }

    public function getMiddleware(): array
    {
        return $this->middlewares;
    }

    protected function db(): Database
    {
        return App::$app->db;
    }

    protected function session(): Session
    {
        return App::$app->session();
    }
}
