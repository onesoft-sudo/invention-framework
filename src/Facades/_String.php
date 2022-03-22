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


use OSN\Framework\Core\Facade;


class _String extends Facade
{
    protected static string $className = \OSN\Framework\DataTypes\_String::class;

    public static function initFacade(...$args)
    {
        return [
            "argsConstructor" => array_slice($args, 0, 1),
            "args" => array_slice($args, 1),
        ];
    }
}
