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
 * Trait CollectionArrayMethods
 * @package OSN\Framework\Extras
 * @todo Add more useful methods to this trait
 */
trait CollectionArrayMethods
{
    public function count(): int
    {
        return count($this->array);
    }

    public function map(Closure $callback)
    {
        return collection(array_map($callback, $this->array));
    }

    public function filter(Closure $callback)
    {
        return collection(array_filter($this->array, $callback));
    }

    public function each(Closure $callback)
    {
        foreach($this->array as $key => $value) {
            call_user_func_array($callback, [$value, $key, $this->array]);
        }

        return $this;
    }

    public function key_exists($key): bool
    {
        return array_key_exists($key, $this->array);
    }

    public function indexOf($value)
    {
        foreach ($this->array as $key => $val) {
            if ($val === $value)
                return $key;
        }

        return null;
    }

    public function sort(bool $descending = false)
    {
        if ($descending)
            rsort($this->array);
        else
            asort($this->array);
    }

    public function isEmpty(): bool
    {
        return empty($this->array);
    }

    public function pop()
    {
        return array_pop($this->array);
    }

    public function shift()
    {
        return array_shift($this->array);
    }

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

    public function push($value)
    {
        $this->array[] = $value;
    }
}
