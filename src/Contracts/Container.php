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

namespace OSN\Framework\Contracts;


use Closure;
use Psr\Container\ContainerInterface;

/**
 * Interface Container
 *
 * @package OSN\Framework\Contracts
 * @author Ar Rakin <rakinar2@gmail.com>
 */
interface Container extends ContainerInterface
{
    /**
     * Binds dependencies into the container.
     *
     * @param $abstract
     * @param Closure $callback
     * @param string|null $prop
     * @param bool $once
     * @return mixed
     */
    public function bind($abstract, Closure $callback, ?string $prop = null, bool $once = false);

    /**
     * Binds dependencies only once (creates an object once).
     *
     * @param $abstract
     * @param Closure $callback
     * @param string|null $prop
     * @return mixed
     */
    public function bindOnce($abstract, Closure $callback, string $prop = null);

    /**
     * Resolve bindings within their property name or class name.
     *
     * @param $abstract
     * @return mixed
     */
    public function resolve($abstract);
}
