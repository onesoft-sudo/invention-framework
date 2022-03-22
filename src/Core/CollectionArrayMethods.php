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

use Closure;

/**
 * Trait CollectionArrayMethods.
 * Contains array_* methods and other utilities for working with collections.
 *
 * @package OSN\Framework\Extras
 * @todo Add more useful methods to this trait
 */
trait CollectionArrayMethods
{
    /**
     * Get the element count.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->array);
    }

    /**
     * Map over the original array return a new collection.
     *
     * @param Closure $callback
     * @return Collection
     */
    public function map(Closure $callback)
    {
        return collection(array_map($callback, $this->array));
    }

    /**
     * Filter the original array return a new filtered collection.
     *
     * @param Closure $callback
     * @return Collection
     */
    public function filter(Closure $callback)
    {
        return collection(array_filter($this->array, $callback));
    }

    /**
     * Iterate over the array.
     *
     * @param Closure $callback
     * @return $this
     */
    public function each(Closure $callback)
    {
        foreach($this->array as $key => $value) {
            call_user_func_array($callback, [$value, $key, $this->array]);
        }

        return $this;
    }

    /**
     * Run array_diff() on the original array.
     *
     * @param Collection $collection
     * @return array
     */
    public function diff(Collection $collection)
    {
        return array_diff($this->array, $collection->array);
    }

    /**
     * Run array_udiff() on the original array.
     *
     * @param Collection $collection
     * @param Closure $callback
     * @return array
     */
    public function udiff(Collection $collection, \Closure $callback)
    {
        return array_udiff($this->array, $collection->array, $callback);
    }

    /**
     * Determine if a key exists.
     *
     * @param $key
     * @return bool
     */
    public function key_exists($key): bool
    {
        return array_key_exists($key, $this->array);
    }

    /**
     * Get the index of a specific value/element in the array.
     *
     * @param $value
     * @return int|string|null
     */
    public function indexOf($value)
    {
        foreach ($this->array as $key => $val) {
            if ($val === $value)
                return $key;
        }

        return null;
    }

    /**
     * Sort the array.
     *
     * @param bool $descending
     */
    public function sort(bool $descending = false)
    {
        if ($descending)
            rsort($this->array);
        else
            asort($this->array);
    }

    /**
     * Determine if the array is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->array);
    }

    /**
     * Run array_pop() on the array.
     *
     * @return mixed|null
     */
    public function pop()
    {
        return array_pop($this->array);
    }

    /**
     * Run array_shift() on the array.
     *
     * @return mixed|null
     */
    public function shift()
    {
        return array_shift($this->array);
    }

    /**
     * Search elements using a regex.
     *
     * @param string $regexp
     * @param bool $index
     * @return array
     */
    public function search(string $regexp, bool $index = false): array
    {
        $out = [];

        foreach ($this->array as $key => $item) {
            $real_item = $item;

            if (is_numeric($item) || $item === 0) {
                $item = $item . '';
            }

            if (is_string($item) && preg_match($regexp, $item)) {
                if ($index)
                    $out[$index] = $real_item;
                else
                    $out[] = $real_item;
            }
        }

        return $out;
    }

    /**
     * Push a value to the array.
     *
     * @param $value
     */
    public function push($value)
    {
        $this->array[] = $value;
    }
}
