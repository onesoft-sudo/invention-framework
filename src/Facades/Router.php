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

namespace OSN\Framework\Facades;

use OSN\Framework\Core\App;
use OSN\Framework\Core\Facade;
use OSN\Framework\Routing\Route;

/**
 * @method static get(string $route, callable $callback): Route
 * @method static post(string $route, callable $callback): Route
 * @method static put(string $route, callable $callback): Route
 * @method static patch(string $route, callable $callback): Route
 * @method static delete(string $route, callable $callback): Route
 * @method static assignAPIController(string $route, string $controller, ?array $handlers = null): Route
 * @method static assignWebController(string $route, string $controller, ?array $handlers = null): Route
 */
class Router extends Facade
{
    protected static string $className = \OSN\Framework\Routing\Router::class;
    protected static bool $override = true;

    public static function init($args)
    {
        self::$object = App::$app->router;
    }
}
