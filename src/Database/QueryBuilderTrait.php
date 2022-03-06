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

use Exception;
use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Model;
use OSN\Framework\Exceptions\QueryException;
use PDO;

trait QueryBuilderTrait
{
    protected Database $db;
    protected string $query = '';
    public array $values = [];
    protected $statement;
    protected string $currentTable = '';
    public string $model = '';

    /**
     * @param string $currentTable
     */
    public function setCurrentTable(string $currentTable): void
    {
        $this->currentTable = $currentTable;
    }

    /**
     * @todo
     */


    protected function choose(array $queries)
    {
        return $this->db->chooseQuery($queries);
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    protected function setQuery(string $query): void
    {
        $this->query = $query;
    }

    public function all(string $table): Collection
    {
        $this->setCurrentTable($table);
        return $this->select($table)->get();
    }

    /**
     * @throws QueryException
     */
    public function insert(string $table, array $data): self
    {
        $this->setCurrentTable($table);

        $values = [];

        foreach ($data as $value) {
            $values[] = $value;
        }

        $keys = implode(', ', array_keys($data));
        $placeholders = implode(',', array_map(function ($value) {
            return '?';
        }, $data));

        try {
            $this->setQuery("INSERT INTO " . $table . "($keys) VALUES($placeholders)");

            $statement = $this->prepare();
            $this->values = $values;
            $this->statement = $statement;
            return $this;
        }
        catch (Exception $e) {
            throw new QueryException($e->getMessage());
        }
    }

    public function update(string $table, array $data): self
    {
        $this->setCurrentTable($table);

        $values = [];

        foreach ($data as $value) {
            $values[] = $value;
        }

        $keys = array_keys($data);
        $queryPart = '';

        foreach ($keys as $key) {
            $queryPart .= " $key = ?,";
        }

        $queryPart = substr($queryPart, 0, strlen($queryPart) - 1);
        $this->setQuery("UPDATE " . $table . " SET $queryPart");
        $this->values = $values;
        return $this;
    }

    public function select(string $table, $columns = []): self
    {
        $this->setCurrentTable($table);

        if(is_string($columns))
            $keys = [$columns];
        elseif(is_array($columns) && empty($columns))
            $keys = ['*'];
        else
            $keys = $columns;

        $queryPart = implode(',', $keys);
        $queryPart = $queryPart[-1] === ',' ? substr($queryPart, 0, strlen($queryPart) - 1) : $queryPart;

        $this->setQuery("SELECT " . $queryPart . " FROM $table");
        return $this;
    }

    public function delete(string $table): self
    {
        $this->setCurrentTable($table);
        $this->setQuery("DELETE FROM $table");
        $this->values = [];
        return $this;
    }

    public function truncate(string $table): self
    {
        $this->setCurrentTable($table);
        $this->setQuery("TRUNCATE TABLE $table;");
        return $this;
    }

    public function addQuery($q, bool $addSelect = false): self
    {
        if ($addSelect) {
            if (!preg_match('/SELECT/', $this->query) && trim($this->query) == '') {
                $table = $this->currentTable;
                $this->query .= "SELECT * FROM $table";
            }
        }

        $this->query .= " $q";
        return $this;
    }

    public function whereCustom($cond, bool $wh = true)
    {
        $wh2 = $wh ? "WHERE" : "";
        return $this->addQuery("$wh2 $cond", true);
    }

    /**
     * @param $cond
     * @param null $valueOrMode
     * @return Query|QueryBuilderTrait|null
     * @todo
     */
    public function where($cond, $valueOrMode = null)
    {
        if (is_string($cond) && $valueOrMode !== null) {
            $this->values[] = $valueOrMode;
            return $this->whereCustom("$cond = ?", !preg_match('/WHERE/i', $this->query));
        }

        if (is_array($cond)) {
            $values = [];
            $q = [];

            foreach ($cond as $cond_item) {
                $q[] = "{$cond_item[0]} {$cond_item[1]} ?";
                $values[] = $cond_item[2];
            }

            $query = implode($valueOrMode === true ? ' OR ' : ' AND ', $q);
            $this->values = array_merge($this->values, $values);
            return $this->whereCustom($query, !preg_match('/WHERE/i', $this->query));
        }

        return null;
    }

    public function orWhere($cond, $valueOrMode = null)
    {
        $this->addQuery("OR");
        return $this->where($cond, $valueOrMode);
    }

    public function andWhere($cond, $valueOrMode = null)
    {
        $this->addQuery("AND");
        return $this->where($cond, $valueOrMode);
    }

    public function orderBy($col, $desc = false)
    {
        $arr = $col;

        if (!is_array($col)) {
            $arr = [
                [$col, $desc]
            ];
        }

        if (!isset($arr[0][0])) {
            $arr = [$arr];
        }

        foreach ($arr as $i => $value) {
            if ($i === 0)
                $this->addQuery("ORDER BY");
            else
                $this->addQuery(", ");

            $this->addQuery("{$value[0]}");
            $this->addQuery(isset($value[1]) && $value[1] === true ? "DESC" : "");
        }

        return $this;
    }

    public function limit($limit, $offset = 0)
    {
        return $this->addQuery("LIMIT $offset, $limit", true);
    }

    public function join(string $table, string $currentTableColumn, string $joinTableColumn)
    {
        return $this->addQuery("JOIN $table ON {$this->currentTable}.$currentTableColumn = $table.$joinTableColumn", true);
    }

    public function leftJoin(string $table, string $currentTableColumn, string $joinTableColumn)
    {
        $this->addQuery("LEFT", true);
        return $this->join($table, $currentTableColumn, $joinTableColumn);
    }

    public function execute($valuesOrQuery = null, bool $prepare = false): bool
    {
        if (is_string($valuesOrQuery)) {
            return $prepare ? $this->db->pdo->prepare($valuesOrQuery) : $this->db->pdo->exec($valuesOrQuery);
        }

        if ($valuesOrQuery === null) {
            $valuesOrQuery = $this->values;
        }

        $this->statement = $this->prepare();
        return $this->statement->execute($valuesOrQuery);
    }

    public function get(): Collection
    {
        $this->execute();
        return collection($this->statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function prepare()
    {
        return $this->db->prepare($this->query);
    }

    public function custom($sql)
    {
        $this->query .= " $sql";
        return $this;
    }
}
