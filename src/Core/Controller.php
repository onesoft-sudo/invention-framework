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

/**
 * The HTTP Controller.
 *
 * @package OSN\Framework\Core
 * @author Ar Rakin <rakinar2@gmail.com>
 */
abstract class Controller
{
    /**
     * Corresponding middleware.
     *
     * @var Middleware[]
     */
    protected array $middlewares = [];

    /**
     * The methods/actions that are under the middleware.
     *
     * @var array
     */
    protected array $middlewareMethods = [];

    /**
     * Render a view.
     *
     * @param $view
     * @param array $data
     * @param string $layout
     * @return View
     * @throws \OSN\Framework\Exceptions\FileNotFoundException
     */
    public function render($view, array $data = [], $layout = ''): View
    {
        if ($view instanceof View) {
            return $view;
        }

        return new View($view, $data, $layout);
    }

    /**
     * Set a custom layout.
     *
     * @param $layout
     */
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
     * Set middleware for this controller.
     *
     * @param Middleware[]|string[] $middlewares
     * @param array $methods
     */
    protected function setMiddleware(array $middlewares, array $methods = [])
    {
        if (is_string($middlewares[0])) {
            foreach ($middlewares as $key => $middleware) {
                $middlewares[$key] = is_string($middleware) ? app()->createNewObject($middleware) : $middleware;
            }
        }

        $this->middlewares = array_merge($this->middlewares, $middlewares);
        $this->middlewareMethods = $methods;
    }

    /**
     * Get method names that are under the middleware.
     *
     * @return array
     */
    public function getMiddlewareMethods(): array
    {
        return $this->middlewareMethods;
    }

    /**
     * Get all middleware of this controller.
     *
     * @return Middleware[]
     */
    public function getMiddleware(): array
    {
        return $this->middlewares;
    }

    /**
     * Get the DB component.
     *
     * @return Database
     */
    protected function db(): Database
    {
        return App::$app->db;
    }

    /**
     * Get the session component.
     *
     * @return Session
     */
    protected function session(): Session
    {
        return App::$app->session();
    }
}
