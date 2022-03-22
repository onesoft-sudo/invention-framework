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


use ArrayAccess;

/**
 * Class Config.
 * The configuration manager.
 *
 * @package OSN\Framework\Core
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Config implements ArrayAccess
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected array $conf;

    /**
     * The configuration file path.
     *
     * @var string
     */
    protected string $conf_file;

    /**
     * Config constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $this->conf_file = $file;
        $this->load();
    }

    /**
     * Get a configuration setting.
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->conf[$name] ?? null;
    }

    /**
     * Set a configuration setting.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->conf[$name] = $value;
    }

    /**
     * Load the configuration.
     *
     * @return void
     */
    protected function load()
    {
       $this->conf = require($this->conf_file);
    }

    /**
     * Get the whole configuration array.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->conf;
    }

    /**
     * Determine if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->conf[$offset]);
    }

    /**
     * Get an offset value.
     *
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->conf[$offset];
    }

    /**
     * Set the given value to the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->conf[$offset] = $value;
    }

    /**
     * Unset an offset.
     *
     * @param mixed $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->conf[$offset]);
    }
}
