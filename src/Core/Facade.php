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

use OSN\Framework\Exceptions\MethodNotFoundException;
use ReflectionException;

/**
 * Class Facade
 * @package OSN\Framework\Core
 */
class Facade
{
    protected static string $className;
    protected static object $object;
    protected static bool $init = true;
    protected static bool $override = false;
    protected static bool $respectConstructor = true;

    public static function init($args)
    {
        self::$object = new static::$className(...$args);
    }

    /**
     * @throws MethodNotFoundException
     */
    public static function __callStatic($name, $arguments)
    {
        if (method_exists(static::$className ?? '', '__construct') && method_exists(static::class, 'initFacade')) {
            $params = static::initFacade(...$arguments);
            $argsConstructor = $params['argsConstructor'];
            $argsToPass = $params['args'];
        }
        else {
            $argsConstructor = [];
            $argsToPass = $arguments;
        }

        if (static::$init)
            static::init($argsConstructor);

        if (method_exists(static::$object, $name) || method_exists(static::$object, "__call")) {
            return call_user_func_array([self::$object, $name], !static::$override ? $argsToPass : $arguments);
        }
        else {
            throw new MethodNotFoundException();
        }
    }
}
