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


/**
 * Add connection between the factories and the models.
 *
 * @package OSN\Framework\Database
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait HasFactory
{
    /**
     * The corresponding factory class.
     *
     * @var string|null
     */
    protected static ?string $factory = null;

    /**
     * Set the factory according the model name or the user input.
     *
     * @param string|null $factory
     */
    public static function setFactory(?string $factory = null)
    {
        if ($factory === null) {
            $array = explode('\\', static::class);
            $modelName = trim(end($array));
            $factoryName = "Database\\Factories\\" . $modelName . 'Factory';

            if (!class_exists($factoryName))
                return;

            static::$factory = $factoryName;
        }
        else {
            static::$factory = $factory;
        }
    }

    /**
     * Get the factory instance.
     *
     * @return Factory
     * @todo Create factory instance only once
     */
    public static function factory(): Factory
    {
        if (static::$factory === null) {
            static::setFactory();
        }

        return new static::$factory();
    }
}
