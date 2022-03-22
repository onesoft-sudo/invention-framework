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

/**
 * Class Collection
 * This is an implementation of array-like object with various useful methods.
 *
 * @package OSN\Framework\Core
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Collection implements JsonSerializable, ArrayAccess, IteratorAggregate, Countable
{
    use CollectionArrayMethods;

    /**
     * The raw array.
     *
     * @var array
     */
    protected array $array;

    /**
     * Collection constructor.
     *
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Get the value of an offset/key.
     *
     * @param null $index
     * @return mixed
     * @throws CollectionException
     */
    public function get($index = null): mixed
    {
        if ($index === null) {
            return $this->array;
        }

        if (!isset($this->array[$index])) {
            throw new CollectionException("Collection key '{$index}' doesn't exist");
        }

        return $this->array[$index];
    }

    /**
     * A wrapper for $this->get()
     *
     * @param $index
     * @return mixed
     * @throws CollectionException
     */
    public function _($index): mixed
    {
        return $this->get($index);
    }

    /**
     * Return the value of the given array offset.
     *
     * @param $key
     * @return mixed
     * @throws CollectionException
     */
    public function __get($key)
    {
        $index = $key;

        if ($key[0] === '_')
            $index = substr($key, 1);

        return $this->_($index);
    }

    /**
     * Set the value of an array offset.
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $index = $key;

        if (is_string($key) && $key[0] === '_')
            $index = substr($key, 1);

        $this->array[$key] = $value;
    }

    /**
     * Set the value of an array offset.
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->__set($key, $value);
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
       return $this->array;
    }

    /**
     * When invoked, just return the raw array.
     *
     * @return array
     */
    public function __invoke(): array
    {
        return $this->array;
    }

    /**
     * Check if the array has the given key.
     *
     * @param $key
     * @return bool
     */
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

    /**
     * Check if the array has the given key, and if it has,
     * then return the value of the key.
     *
     * @param $key
     * @return mixed|null
     * @throws CollectionException
     */
    public function hasGet($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return null;
    }

    /**
     * Determine if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->array[$offset]);
    }

    /**
     * Get the value of an offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    /**
     * Set the given value to the offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    /**
     * Unset an offset.
     *
     * @param mixed $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     * Retrieve an external iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->array);
    }
}
