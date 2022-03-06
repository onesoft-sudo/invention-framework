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

namespace OSN\Framework\ORM;


use Closure;
use OSN\Framework\Database\Query;
use OSN\Framework\Database\UniversalQueryBuilderTrait;
use OSN\Framework\Exceptions\MethodNotFoundException;

abstract class Relationship
{
    use UniversalQueryBuilderTrait;

    protected Query $query;

    abstract protected function makeQuery();

    public function __construct()
    {
        $this->query = new Query();
        $this->makeQuery();
    }

    public function get()
    {
        return $this->query->get();
    }

    protected function tableToForeignColumn(string $table, string $append = '_id')
    {
        return preg_replace('/s$/', $append, $table);
    }

    public function custom(Closure $callback): self
    {
        call_user_func_array($callback, [$this->query]);
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query->getQuery();
    }
}
