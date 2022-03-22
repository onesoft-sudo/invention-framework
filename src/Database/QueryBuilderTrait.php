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

/**
 * The methods for building an SQL query.
 *
 * @package OSN\Framework\Database
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait QueryBuilderTrait
{
    /**
     * The database instance.
     *
     * @var Database
     */
    protected Database $db;

    /**
     * The SQL query.
     *
     * @var string
     */
    protected string $query = '';

    /**
     * The values to bind.
     *
     * @var array
     */
    public array $values = [];

    /**
     * The PDO statement.
     *
     * @var mixed
     */
    protected $statement;

    /**
     * Current table name.
     *
     * @var string
     */
    protected string $currentTable = '';

    /**
     * The current model.
     *
     * @var string
     */
    public string $model = '';

    /**
     * Set current table.
     *
     * @param string $currentTable
     */
    public function setCurrentTable(string $currentTable): void
    {
        $this->currentTable = $currentTable;
    }

    /**
     * Choose appropriate query from multiple queries according to the DB.
     *
     * @param array $queries
     * @return mixed
     */
    protected function choose(array $queries)
    {
        return $this->db->chooseQuery($queries);
    }

    /**
     * Get the generated SQL.
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Set the query.
     *
     * @param string $query
     */
    protected function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * Get all rows.
     *
     * @param string $table
     * @return Collection
     */
    public function all(string $table): Collection
    {
        $this->setCurrentTable($table);
        return $this->select($table)->get();
    }

    /**
     * Build a 'INSERT INTO xyz(...) VALUES(...)` query.
     *
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

    /**
     * Build a `UPDATE xyz SET ...` query.
     *
     * @param string $table
     * @param array $data
     * @return QueryBuilderTrait|Query
     */
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

    /**
     * Build a `SELECT ... FROM xyz` query.
     *
     * @param string $table
     * @param array $columns
     * @param bool $distinct
     * @param bool $set
     * @return $this
     */
    public function select(string $table, $columns = [], bool $distinct = false, bool $set = true): self
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

        $m = $set ? 'setQuery' : 'addQuery';

        $this->$m("SELECT " . ($distinct ? 'DISTINCT ' : '') . $queryPart . " FROM $table");

        return $this;
    }

    /**
     * Build a `SELECT DISTINCT(...) FROM xyz` query.
     *
     * @param string $table
     * @param array $columns
     * @return $this
     */
    public function selectDistinct(string $table, $columns = []): self
    {
        return $this->select($table, $columns, true);
    }

    /**
     * Select columns from raw input.
     *
     * @param string $stmt
     * @return $this
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    public function selectRaw(string $stmt): self
    {
        return $this->addQuery("SELECT $stmt FROM {$this->currentTable}");
    }

    /**
     * Build a `DELETE FROM xyz` query.
     *
     * @param string $table
     * @return $this
     */
    public function delete(string $table): self
    {
        $this->setCurrentTable($table);
        $this->setQuery("DELETE FROM $table");
        $this->values = [];
        return $this;
    }

    /**
     * Build a `TRUNCATE TABLE xyz` query.
     *
     * @param string $table
     * @return $this
     */
    public function truncate(string $table): self
    {
        $this->setCurrentTable($table);
        $this->setQuery("TRUNCATE TABLE $table;");
        return $this;
    }

    /**
     * Append strings to query.
     *
     * @param $q
     * @param bool $addSelect Determine if the method should add a `SELECT` statement before the given string.
     * @return $this
     */
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

    /**
     * Set a where condition from raw input.
     *
     * @param $cond
     * @param bool $wh
     * @return Query|QueryBuilderTrait
     */
    public function whereCustom($cond, bool $wh = true)
    {
        $wh2 = $wh ? "WHERE" : "";
        return $this->addQuery("$wh2 $cond", true);
    }

    /**
     * Add a where clause.
     *
     * @param string|array $cond
     * @param null $valueOrMode
     * @param null $operator
     * @return Query|QueryBuilderTrait|null
     */
    public function where(string|array $cond, $valueOrMode = null, $operator = null)
    {
        if (is_string($cond)) {
            $this->values[] = $valueOrMode;
            $operator = $operator ?? '=';
            $cond = $cond ?? '';
            return $this->whereCustom("$cond $operator ?", !preg_match('/WHERE/i', $this->query));
        }

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

    /**
     * Add a `OR [cond]` expression to the where condition.
     *
     * @param $cond
     * @param null $valueOrMode
     * @return Query|QueryBuilderTrait|null
     */
    public function orWhere($cond, $valueOrMode = null)
    {
        $this->addQuery("OR");
        return $this->where($cond, $valueOrMode);
    }

    /**
     * Add a `AND [cond]` expression to the where condition.
     *
     * @param $cond
     * @param null $valueOrMode
     * @return Query|QueryBuilderTrait|null
     */
    public function andWhere($cond, $valueOrMode = null)
    {
        $this->addQuery("AND");
        return $this->where($cond, $valueOrMode);
    }

    /**
     * Add a `WHERE [column] LIKE [exp]` expression to the where condition.
     *
     * @param $col
     * @param null $value
     * @return Query|QueryBuilderTrait
     */
    public function whereLike($col, $value = null)
    {
        $this->values[] = $value;
        return $this->addQuery((!preg_match('/WHERE/i', $this->query) ? "WHERE " : "") . "$col LIKE ?");
    }

    /**
     * Add a `WHERE [column] IN([values])` expression to the where condition.
     *
     * @param $col
     * @param array $value
     * @param bool $not
     * @return Query|QueryBuilderTrait
     */
    public function whereIn($col, array $value, bool $not = false)
    {
        $this->values = array_merge($this->values, $value);
        $questionMarks = implode(', ', array_map(fn() => '?', $value));
        return $this->addQuery((!preg_match('/WHERE/i', $this->query) ? "WHERE " : "") . "$col " . ($not ? "NOT " : "") .  "IN($questionMarks)");
    }

    /**
     * Add a `WHERE [column] NOT IN([values])` expression to the where condition.
     *
     * @param $col
     * @param array $value
     * @return Query|QueryBuilderTrait
     */
    public function whereNotIn($col, array $value)
    {
        return $this->whereIn($col, $value);
    }

    /**
     * Add a `WHERE [column] IS NULL` expression to the where condition.
     *
     * @param $col
     * @return Query|QueryBuilderTrait
     */
    public function whereIsNull($col)
    {
        return $this->addQuery((!preg_match('/WHERE/i', $this->query) ? "WHERE " : "") . "$col IS NULL");
    }

    /**
     * Add a `WHERE [column] IS NOT NULL` expression to the where condition.
     *
     * @param $col
     * @return Query|QueryBuilderTrait
     */
    public function whereIsNotNull($col)
    {
        return $this->addQuery((!preg_match('/WHERE/i', $this->query) ? "WHERE " : "") . "$col IS NOT NULL");
    }

    /**
     * Append `AND`.
     *
     * @return Query|QueryBuilderTrait
     */
    public function and()
    {
        return $this->addQuery('AND');
    }

    /**
     * Append `OR`.
     *
     * @return Query|QueryBuilderTrait
     */
    public function or()
    {
        return $this->addQuery('OR');
    }

    /**
     * Append `NOT`.
     *
     * @return Query|QueryBuilderTrait
     */
    public function not()
    {
        return $this->addQuery('NOT');
    }

    /**
     * Add a `WHERE [column] BETWEEN [min] AND [max]` expression to the where condition.
     *
     * @param $col
     * @param $min
     * @param $max
     * @param bool $not
     * @return Query|QueryBuilderTrait
     */
    public function whereBetween($col, $min, $max, bool $not = false)
    {
        $this->values[] = $min;
        $this->values[] = $max;

        return $this->addQuery((!preg_match('/WHERE/i', $this->query) ? "WHERE " : "") . "$col " . ($not ? "NOT " : "") .  "BETWEEN ? AND ?");
    }

    /**
     * Add an `ORDER BY` clause.
     *
     * @param $col
     * @param false $desc
     * @return $this
     */
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

    /**
     * Add a limit.
     *
     * @param $limit
     * @param int $offset
     * @return Query|QueryBuilderTrait
     */
    public function limit($limit, $offset = 0)
    {
        return $this->addQuery("LIMIT $offset, $limit", true);
    }

    /**
     * Add a GROUP BY clause.
     *
     * @param string $groupBy
     * @return Query|QueryBuilderTrait
     */
    public function groupBy(string $groupBy)
    {
        return $this->addQuery("GROUP BY $groupBy", true);
    }

    /**
     * Add a HAVING clause.
     *
     * @param string $condition
     * @return Query|QueryBuilderTrait
     */
    public function having(string $condition)
    {
        return $this->addQuery((!preg_match('/HAVING/i', $this->query) ? "HAVING " : "") . "$condition", true);
    }

    /**
     * Add an UNION clause.
     *
     * @param string|\Stringable|Query|Table $query
     * @return Query|QueryBuilderTrait
     */
    public function union(string|\Stringable|Query|Table $query)
    {
        if (is_object($query) && property_exists($query, 'values'))
            $this->values = array_merge($this->values, $query->values);

        return $this->addQuery("UNION $query");
    }

    /**
     * Add a simple JOIN statement.
     *
     * @param string $table
     * @param string $currentTableColumn
     * @param string $joinTableColumn
     * @return Query|QueryBuilderTrait
     */
    public function join(string $table, string $currentTableColumn, string $joinTableColumn)
    {
        return $this->addQuery("JOIN $table ON {$this->currentTable}.$currentTableColumn = $table.$joinTableColumn", true);
    }

    /**
     * Add a simple LEFT JOIN statement.
     *
     * @param string $table
     * @param string $currentTableColumn
     * @param string $joinTableColumn
     * @return Query|QueryBuilderTrait
     */
    public function leftJoin(string $table, string $currentTableColumn, string $joinTableColumn)
    {
        $this->addQuery("LEFT", true);
        return $this->join($table, $currentTableColumn, $joinTableColumn);
    }

    /**
     * Add a simple RIGHT JOIN statement.
     *
     * @param string $table
     * @param string $currentTableColumn
     * @param string $joinTableColumn
     * @return Query|QueryBuilderTrait
     */
    public function rightJoin(string $table, string $currentTableColumn, string $joinTableColumn)
    {
        $this->addQuery("RIGHT", true);
        return $this->join($table, $currentTableColumn, $joinTableColumn);
    }

    /**
     * Add a simple INNER JOIN statement.
     *
     * @param string $table
     * @param string $currentTableColumn
     * @param string $joinTableColumn
     * @return Query|QueryBuilderTrait
     */
    public function innerJoin(string $table, string $currentTableColumn, string $joinTableColumn)
    {
        $this->addQuery("INNER", true);
        return $this->join($table, $currentTableColumn, $joinTableColumn);
    }

    /**
     * Add a simple CROSS JOIN statement.
     *
     * @param string $table
     * @param string $currentTableColumn
     * @param string $joinTableColumn
     * @return Query|QueryBuilderTrait
     */
    public function crossJoin(string $table, string $currentTableColumn, string $joinTableColumn)
    {
        $this->addQuery("CROSS", true);
        return $this->join($table, $currentTableColumn, $joinTableColumn);
    }

    /**
     * Add a simple FULL JOIN statement.
     *
     * @param string $table
     * @param string $currentTableColumn
     * @param string $joinTableColumn
     * @return Query|QueryBuilderTrait
     */
    public function fullJoin(string $table, string $currentTableColumn, string $joinTableColumn)
    {
        $this->addQuery("FULL OUTER", true);
        return $this->join($table, $currentTableColumn, $joinTableColumn);
    }

    /**
     * Add a custom JOIN statement.
     *
     * @param string $table
     * @param string $on
     * @return Query|QueryBuilderTrait
     */
    public function joinRaw(string $table, string $on)
    {
        return $this->addQuery("JOIN $table ON $on", true);
    }

    /**
     * Add a custom LEFT JOIN statement.
     *
     * @param string $table
     * @param string $on
     * @return Query|QueryBuilderTrait
     */
    public function leftJoinRaw(string $table, string $on)
    {
        $this->addQuery("LEFT", true);
        return $this->joinRaw($table, $on);
    }

    /**
     * Add a custom RIGHT JOIN statement.
     *
     * @param string $table
     * @param string $on
     * @return Query|QueryBuilderTrait
     */
    public function rightJoinRaw(string $table, string $on)
    {
        $this->addQuery("RIGHT", true);
        return $this->joinRaw($table, $on);
    }

    /**
     * Add a custom INNER JOIN statement.
     *
     * @param string $table
     * @param string $on
     * @return Query|QueryBuilderTrait
     */
    public function innerJoinRaw(string $table, string $on)
    {
        $this->addQuery("INNER", true);
        return $this->joinRaw($table, $on);
    }

    /**
     * Add a custom CROSS JOIN statement.
     *
     * @param string $table
     * @param string $on
     * @return Query|QueryBuilderTrait
     */
    public function crossJoinRaw(string $table, string $on)
    {
        $this->addQuery("CROSS", true);
        return $this->joinRaw($table, $on);
    }

    /**
     * Add a custom FULL JOIN statement.
     *
     * @param string $table
     * @param string $on
     * @return Query|QueryBuilderTrait
     */
    public function fullJoinRaw(string $table, string $on)
    {
        $this->addQuery("FULL OUTER", true);
        return $this->joinRaw($table, $on);
    }

    /**
     * Add a `INSERT INTO foo(...) SELECT (...) FROM bar` type statement.
     *
     * @param string $table1
     * @param string $table2
     * @param array $columns1
     * @param array $columns2
     * @param bool $distinct
     * @return Query|QueryBuilderTrait
     */
    public function insertSelect(string $table1, string $table2, array $columns1 = [], array $columns2 = [], bool $distinct = false)
    {
        $params = '';

        if (!empty($columns1)) {
            $params = implode(', ', $columns1);
        }

        $this->addQuery("INSERT INTO $table1" . (empty($columns1) ? "" : " ($params)"));
        return $this->select($table2, $columns2, $distinct,false);
    }

    /**
     * Add a `SELECT INTO` statement.
     *
     * @param string $table1
     * @param string $table2
     * @param array $columns
     * @param string $in
     * @return Query|QueryBuilderTrait
     */
    public function selectInto(string $table1, string $table2, array $columns = [], string $in = '')
    {
        $this->setCurrentTable($table1);

        $params = '*';

        if (!empty($columns)) {
            $params = implode(', ', $columns);
        }

        return $this->addQuery("SELECT $params INTO $table2" . ($in === '' ? '' : " IN $in") . " FROM $table1");
    }

    /**
     * Execute the query.
     *
     * @param null $valuesOrQuery
     * @param bool $prepare
     * @return bool
     */
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

    /**
     * Execute the query and fetch the result.
     *
     * @return Collection
     */
    public function get(): Collection
    {
        $this->execute();
        return collection($this->statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Prepare a statement.
     *
     * @return false|\PDOStatement
     */
    public function prepare()
    {
        return $this->db->prepare($this->query);
    }

    /**
     * Add custom SQL.
     *
     * @param $sql
     * @return $this
     */
    public function custom($sql)
    {
        $this->query .= " $sql";
        return $this;
    }
}
