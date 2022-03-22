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

namespace OSN\Framework\View;


use OSN\Framework\DataTypes\_String;
use OSN\Framework\PowerParser\HTMLAttributes;

abstract class Component implements \OSN\Framework\Contracts\Component
{
    private array $attributes = [];

    public final function __construct(array $data)
    {
        $this->attributes = $data;
        call_user_func_array([$this, 'boot'], array_values($data));
    }

    public function __toString()
    {
        return $this->render() . '';
    }

    protected function view(string $view, array $data = [], $layout = ''): View
    {
        $data = array_merge($this->dataArray(), $data, ['__attributes' => new HTMLAttributes($this->attributes)]);
        return view($view, $data, $layout);
    }

    protected function dataArray(): array
    {
        return (array) $this;
    }
}