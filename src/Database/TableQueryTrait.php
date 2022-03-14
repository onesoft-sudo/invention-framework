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

namespace OSN\Framework\Database;


use Error;
use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Database;

trait TableQueryTrait
{
    protected ?string $tableName;
    public string $primaryKey = 'id';
    protected Database $db;
    public Query $query;


    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    public function all(): Collection
    {
        return $this->query->all($this->getTableName());
    }

    public function insert(array $data)
    {
        return $this->query->insert($this->getTableName(), $data);
    }

    public function patch(array $data)
    {
        return $this->query->update($this->getTableName(), $data);
    }

    public function select(array $data = ['*'])
    {
        return $this->query->select($this->getTableName(), $data);
    }

    public function insertSelect(string $table, array $columns1 = [], array $columns2 = [], bool $distinct = false)
    {
        return $this->query->insertSelect($this->getTableName(), $table, $columns1, $columns2, $distinct);
    }

    public function selectInto(string $table, array $columns = [], string $in = '')
    {
        return $this->query->selectInto($this->getTableName(), $table, $columns, $in);
    }

    public function delete()
    {
        return $this->query->delete($this->getTableName());
    }

    public function truncate()
    {
        return $this->query->truncate($this->getTableName());
    }

    public function __call($name, $args)
    {
        if (method_exists($this->query, $name)) {
            return call_user_func_array([$this->query, $name], $args);
        }

        throw new Error("Call to undefined method " . get_class($this) . '::' . $name . '()');
    }

    public static function __callStatic($name, $args)
    {
        $obj = new static();
        if (method_exists($obj->query, $name)) {
            return call_user_func_array([$obj->query, $name], $args);
        }

        throw new Error("Call to undefined method " . get_class($obj) . '::' . $name . '()');
    }
}
