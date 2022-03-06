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

namespace OSN\Framework\Database;


use OSN\Framework\Facades\File;

trait HasFactory
{
    protected static ?string $factory = null;

    public static function setFactory(?string $factory = null)
    {
        if ($factory === null) {
            $array = explode('\\', self::class);
            $modelName = trim(end($array));
            $factoryName = "Database\\Factories\\" . $modelName . 'Factory';

            if (!class_exists($factoryName))
                return;

            self::$factory = $factoryName;
        }
        else {
            self::$factory = $factory;
        }
    }

    public static function factory(): Factory
    {
        self::setFactory();
        return new self::$factory();
    }
}
