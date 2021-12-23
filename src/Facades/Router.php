<?php

namespace OSN\Framework\Facades;

use OSN\Framework\Core\App;
use OSN\Framework\Core\Facade;

/**
 * @method static get(string $route, array $array)
 * @method static post(string $route, array $array)
 * @method static put(string $route, array $array)
 * @method static patch(string $route, array $array)
 * @method static delete(string $route, array $array)
 */
class Router extends Facade
{
    public static function init()
    {
        self::$object = App::$app->router;
    }
}
