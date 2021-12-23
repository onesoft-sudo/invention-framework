<?php

namespace OSN\Framework\Facades;

use OSN\Framework\Core\App;
use OSN\Framework\Core\Facade;

class Request extends Facade
{
    public static function init()
    {
        self::$object = App::$app->request;
    }
}
