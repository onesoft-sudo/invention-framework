<?php

namespace OSN\Framework\Facades;

use OSN\Framework\Core\App;
use OSN\Framework\Core\Facade;

/**
 * @method static get(string $route, callable $callback): Route
 * @method static post(string $route, callable $callback): Route
 * @method static put(string $route, callable $callback): Route
 * @method static patch(string $route, callable $callback): Route
 * @method static delete(string $route, callable $callback): Route
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
