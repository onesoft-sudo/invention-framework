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
 * This trait is used in some places, to resolve non-existing methods from the
 * internal query instance.
 *
 * @package OSN\Framework\Database
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait UniversalQueryBuilderTrait
{
    /**
     * If the method doesn't exist, then attempt to call the method into the query instance.
     *
     * @param string $name
     * @param array $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->query, $name)) {
            call_user_func_array([$this->query, $name], $arguments);
            return $this;
        }

        throw new MethodNotFoundException('Call to undefined method ' . static::class . '::' . $name . '()');
    }
}
