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

namespace OSN\Framework\Console;


use OSN\Framework\Core\Collection;

/**
 * Class ArgumentCollection
 *
 * @package OSN\Framework\Console
 * @author Ar Rakin <rakinar2@gmail.com>
 * @deprecated
 */
class ArgumentCollection extends Collection
{
    public function hasOption(string $option): bool
    {
        foreach ($this->array as $key => $arg) {
            if (trim($arg) === trim($option)) {
                return true;
            }

            if (preg_match("/^" . trim($option) . "=(.*)/", $arg)) {
                return true;
            }
        }

        return false;
    }

    public function optionHasValue(string $option): bool
    {
        $bool = $this->getOptionValue($option);

        if ($bool === false || trim($bool) == '') {
            return false;
        }

        return true;
    }

    public function getOptionValue(string $option)
    {
        foreach ($this->array as $key => $arg) {
            if (trim($arg) === trim($option)) {
                return $this->array[$key + 1] ?? false;
            }

            if (preg_match("/^" . trim($option) . "=(.*)/", $arg)) {
                $array = explode("=", $arg);
                return end($array);
            }
        }

        return false;
    }

    public function getArgNoOption(int $index = 0)
    {
        $args = $this->array;

        $args = array_filter($args, function ($value) {
            return ($value ?? [' '])[0] !== '-';
        });

        $i = 0;
        $args2 = [];

        foreach ($args as $key => $value) {
            $args2[$i] = $value;
            $i++;
        }

        return $args2[$index] ?? null;
    }
}
