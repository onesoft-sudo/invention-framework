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
use Countable;
use Exception;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use OSN\Framework\Exceptions\CollectionException;
use Traversable;

class Collection implements JsonSerializable, ArrayAccess, IteratorAggregate, Countable
{
    use CollectionArrayMethods;

    protected array $array;

    /**
     * Collection constructor.
     * @param array $array
     */
    public function __construct(array $array)
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $newArray[$key] = $value;
        }

        $this->array = $newArray;
    }

    /**
     * @throws CollectionException
     */
    public function get($index = null)
    {
        if ($index === null) {
            return $this->array;
        }

        if (!isset($this->array[$index])) {
            throw new CollectionException("Collection key '{$index}' doesn't exist");
        }

        return $this->array[$index];
    }

    public function _($index)
    {
        return $this->get($index);
    }

    public function __get($key)
    {
        $index = $key;

        if ($key[0] === '_')
            $index = substr($key, 1);

        return $this->_($index);
    }

    public function __set($key, $value)
    {
        $index = $key;

        if (is_string($key) && $key[0] === '_')
            $index = substr($key, 1);

        $this->array[$key] = $value;
    }

    public function set($key, $value)
    {
        $this->__set($key, $value);
    }

    public function jsonSerialize(): array
    {
       return $this->array;
    }

    public function __invoke(): array
    {
        return $this->array;
    }

    public function has($key): bool
    {
        try {
            $tmp = $this->get($key);
            return true;
        }
        catch (CollectionException $e) {
            return false;
        }
    }

    public function hasGet($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return null;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->array[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    public function diff(Collection $newUsers)
    {
        return array_diff($this->array, $newUsers->array);
    }

    public function udiff(Collection $newUsers, \Closure $callback)
    {
        return array_udiff($this->array, $newUsers->array, $callback);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->array);
    }
}
