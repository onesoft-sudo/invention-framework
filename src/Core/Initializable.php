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
 * Loads the initializers step-by-step.
 *
 * @package OSN\Framework\Core
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait Initializable
{
    /**
     * The initializers list.
     *
     * @var array
     */
    protected array $initializers = [];

    /**
     * Load all initializers from the config.
     *
     * @return void
     */
    public function loadInitializers()
    {
        foreach (config('initializers') as $value) {
            $this->initializers[] = new $value();
        }
    }

    /**
     * Run preinit() method of all initializers.
     *
     * @return void
     */
    public function preinit()
    {
        foreach ($this->initializers as $initializer) {
            if (isCLI() && $initializer->cgi === true)
                continue;
            if (!isCLI() && $initializer->cgi === false)
                continue;

            $initializer->setApp(app());
            $initializer->preinit();
        }
    }

    /**
     * Run init() method of all initializers.
     *
     * @return void
     */
    public function init()
    {
        foreach ($this->initializers as $initializer) {
            if (isCLI() && $initializer->cgi === true)
                continue;
            if (!isCLI() && $initializer->cgi === false)
                continue;

            $initializer->setApp(app());
            $initializer->init();
        }
    }

    /**
     * Run afterinit() method of all initializers.
     *
     * @return void
     */
    public function afterinit()
    {
        foreach ($this->initializers as $initializer) {
            if (isCLI() && $initializer->cgi === true)
                continue;
            if (!isCLI() && $initializer->cgi === false)
                continue;

            $initializer->setApp(app());
            $initializer->afterinit();
        }
    }
}
