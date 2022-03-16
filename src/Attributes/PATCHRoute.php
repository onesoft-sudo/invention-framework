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

namespace OSN\Framework\Attributes;

use Attribute;
use Pure;

/**
 * Represents a PATCH route.
 *
 * @package OSN\Framework\Attributes
 * @author Ar Rakin <rakinar2@gmail.com>
 */
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class PATCHRoute extends Route
{
    /**
     * PATCHRoute constructor.
     *
     * @param string $route
     * @param string $name
     */
    #[Pure]
    public function __construct(string $route, string $name = '')
    {
        parent::__construct($route, 'PATCH', $name);
    }
}