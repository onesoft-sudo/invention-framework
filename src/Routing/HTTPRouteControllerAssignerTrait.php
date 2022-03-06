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

/**
 * Trait HTTPMethodControllerHelper
 *
 * @package OSN\Framework\Http
 * @author Ar Rakin <rakinar2@gmail.com>
 * @todo Update route params
 */
trait HTTPRouteControllerAssignerTrait
{
    protected array $apiHandlers = [
        "get" => ["index", "view"],
        "post" => "store",
        "put" => "update",
        "patch" => "update",
        "delete" => "delete",
    ];

    protected array $webHandlers = [
        "get" => ["index", "view", 'create', 'edit'],
        "post" => "store",
        "put" => "update",
        "patch" => "update",
        "delete" => "delete",
    ];

    public function assignAPIController(string $route, string $controller, ?array $handlers = null)
    {
        if (class_exists($controller)) {
            if ($handlers !== null) {
                $this->apiHandlers = $handlers;
            }

            $array = explode("\\", $controller);
            $name = strtolower(preg_replace('/Controller$/', '', end($array)));

            $this->get($route, [$controller, $this->apiHandlers['get'][0]])->name($name . "." . $this->apiHandlers['get'][0]);

            $this->get($route . "/(\d+)", [$controller, $this->apiHandlers['get'][1]])->name($name . "." . $this->apiHandlers['get'][1]);

            $this->post($route, [$controller, $this->apiHandlers['post']])->name($name . "." . $this->apiHandlers['post']);

            $this->put($route . '/(\d+)', [$controller, $this->apiHandlers['put']])->name($name . "." . $this->apiHandlers['put']);
            $this->patch($route . '/(\d+)', [$controller, $this->apiHandlers['patch']])->name($name . "." . $this->apiHandlers['patch']);

            $this->delete($route . '/(\d+)', [$controller, $this->apiHandlers['delete']])->name($name . "." . $this->apiHandlers['delete']);
        }
    }

    /**
     * @param string $route
     * @param string $controller
     * @param array|null $handlers
     * @todo
     */
    public function assignWebController(string $route, string $controller, ?array $handlers = null)
    {
        if (class_exists($controller)) {
            if ($handlers !== null) {
                $this->webHandlers = $handlers;
            }

            $array = explode("\\", $controller);
            $name = strtolower(preg_replace('/Controller$/', '', end($array)));

            $this->get($route, [$controller, $this->webHandlers['get'][0]])->name($name . "." . $this->webHandlers['get'][0]);

            $this->get($route . "/(\d+)", [$controller, $this->webHandlers['get'][1]])->name($name . "." . $this->webHandlers['get'][1]);

            $this->get($route . '/create', [$controller, $this->webHandlers['get'][2]])->name($name . "." . $this->webHandlers['get'][2]);
            $this->post($route, [$controller, $this->webHandlers['post']])->name($name . "." . $this->webHandlers['post']);

            $this->get($route . '/(\d+)/edit', [$controller, $this->webHandlers['get'][3]])->name($name . "." . $this->webHandlers['get'][3]);
            $this->put($route . '/(\d+)', [$controller, $this->webHandlers['put']])->name($name . "." . $this->webHandlers['put']);
            $this->patch($route . '/(\d+)', [$controller, $this->webHandlers['patch']])->name($name . "." . $this->webHandlers['patch']);

            $this->delete($route . '/(\d+)', [$controller, $this->webHandlers['delete']])->name($name . "." . $this->webHandlers['delete']);
        }
    }
}
