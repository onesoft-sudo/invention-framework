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

/**
 * Helps building a query while using \OSN\Framework\Database\Table class.
 *
 * @package OSN\Framework\Database
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait TableQueryTrait
{
    /**
     * The table name.
     *
     * @var string|null
     */
    protected ?string $tableName;

    /**
     * Table primary key.
     *
     * @var string
     */
    public string $primaryKey = 'id';

    /**
     * The database component.
     *
     * @var Database
     */
    protected Database $db;

    /**
     * The query instance.
     *
     * @var Query
     */
    public Query $query;

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Set the table name.
     *
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * Get all rows.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->query->all($this->getTableName());
    }

    /**
     * Prepare an INSERT query.
     *
     * @param array $data
     * @return Query
     * @throws \OSN\Framework\Exceptions\QueryException
     */
    public function insert(array $data)
    {
        return $this->query->insert($this->getTableName(), $data);
    }

    /**
     * Prepare an UPDATE query.
     *
     * @param array $data
     * @return Query
     * @todo RENAME
     */
    public function patch(array $data)
    {
        return $this->query->update($this->getTableName(), $data);
    }

    /**
     * Prepare a SELECT query.
     *
     * @param array|string[] $data
     * @return Query|QueryBuilderTrait
     */
    public function select(array $data = ['*'])
    {
        return $this->query->select($this->getTableName(), $data);
    }

    /**
     * Prepare an INSERT INTO ... SELECT query.
     *
     * @param string $table
     * @param array $columns1
     * @param array $columns2
     * @param bool $distinct
     * @return Query|QueryBuilderTrait
     */
    public function insertSelect(string $table, array $columns1 = [], array $columns2 = [], bool $distinct = false)
    {
        return $this->query->insertSelect($this->getTableName(), $table, $columns1, $columns2, $distinct);
    }

    /**
     * Prepare an SELECT INTO query.
     *
     * @param string $table
     * @param array $columns
     * @param string $in
     * @return Query|QueryBuilderTrait
     */
    public function selectInto(string $table, array $columns = [], string $in = '')
    {
        return $this->query->selectInto($this->getTableName(), $table, $columns, $in);
    }

    /**
     * Prepare a DELETE query.
     *
     * @return Query|QueryBuilderTrait
     */
    public function delete()
    {
        return $this->query->delete($this->getTableName());
    }

    /**
     * Prepare a TRUNCATE query.
     *
     * @return Query|QueryBuilderTrait
     */
    public function truncate()
    {
        return $this->query->truncate($this->getTableName());
    }

    /**
     * If the method doesn't exist, then attempt to call the method into the query instance.
     *
     * @param string $name
     * @param array $args
     * @return false|mixed
     */
    public function __call($name, $args)
    {
        if (method_exists($this->query, $name)) {
            return call_user_func_array([$this->query, $name], $args);
        }

        throw new Error("Call to undefined method " . get_class($this) . '::' . $name . '()');
    }

    /**
     * If the static method doesn't exist, then attempt to call the method into the newly created
     * query instance.
     *
     * @param string $name
     * @param array $args
     * @return false|mixed
     */
    public static function __callStatic($name, $args)
    {
        $obj = new static();
        if (method_exists($obj->query, $name)) {
            return call_user_func_array([$obj->query, $name], $args);
        }

        throw new Error("Call to undefined method " . get_class($obj) . '::' . $name . '()');
    }
}
