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

/**
 * The abstract base initializer.
 *
 * @package OSN\Framework\Core
 * @author Ar Rakin <rakinar2@gmail.com>
 */
abstract class Initializer
{
    /**
     * The application instance.
     *
     * @var \OSN\Framework\Foundation\App
     */
    protected $app;

    /**
     * Determine if the initializer should run on CLI, CGI or both.
     * CGI = true, CLI = false, Both = null
     *
     * @var bool|null
     */
    public ?bool $cgi = null;

    /**
     * Initialize the application and its services.
     *
     * @return void
     */
    abstract public function init();

    /**
     * Initialize the application and its services before
     * other services and components are ready.
     *
     * @return void
     */
    abstract public function preinit();

    /**
     * Configure the services after application initialization.
     *
     * @return void
     */
    abstract public function afterinit();

    /**
     * Set the application instance.
     *
     * @param \OSN\Framework\Foundation\App $app
     */
    public function setApp(\OSN\Framework\Foundation\App $app)
    {
        $this->app = $app;
    }
}
