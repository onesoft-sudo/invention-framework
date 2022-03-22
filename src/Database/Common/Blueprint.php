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

namespace OSN\Framework\Database\Common;


use OSN\Framework\Console\App;
use OSN\Framework\Core\Model;
use OSN\Framework\Database\MySQL\Column as MySQLColumn;
use OSN\Framework\Database\SQLite\Column as SQLiteColumn;

/**
 * Common table blueprint.
 *
 * @package OSN\Framework\Database\Common
 * @author Ar Rakin <rakinar2@gmail.com>
 */
abstract class Blueprint
{
    /**
     * Table name.
     *
     * @var string
     */
    protected string $table;

    /**
     * SQL starting string.
     *
     * @var string
     */
    protected string $sqlStart = '';

    /**
     * SQL ending string.
     *
     * @var string
     */
    protected string $sqlEnd = '';

    /**
     * Columns of the table.
     *
     * @var SQLiteColumn[]|MySQLColumn[]
     */
    protected array $columns = [];

    /**
     * Blueprint constructor.
     *
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Set SQL start.
     *
     * @param string $sqlStart
     */
    public function setSQLStart(string $sqlStart): void
    {
        $this->sqlStart = $sqlStart;
    }

    /**
     * Set SQL end.
     *
     * @param string $sqlEnd
     */
    public function setSQLEnd(string $sqlEnd): void
    {
        $this->sqlEnd = $sqlEnd;
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSQL();
    }

    /**
     * Invoke the object.
     *
     * @return string
     */
    public function __invoke(): string
    {
        return $this->getSQL();
    }

    /**
     * Get SQL query.
     *
     * @return string
     */
    public function getSQL(): string
    {
        $sqlMain = '';

        foreach ($this->columns as $column) {
            $sqlMain .= "\n" . $column() . ",";
        }

        $sqlMain = $this->sqlStart . substr($sqlMain, 0, strlen($sqlMain) - 1) . "\n" . $this->sqlEnd;
        $sqlMain = str_replace("{{ table }}", "{$this->table}", $sqlMain);

        return "{$sqlMain}";
    }

    /**
     * Get all columns.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Add a column.
     *
     * @param string $type
     * @param string $column
     * @param string $attrs
     * @param bool $colname
     * @return Column
     */
    public function add(string $type, string $column, string $attrs = '', bool $colname = true): Column
    {
        if(app()->db->getVendor() === 'sqlite')
            $col = new SQLiteColumn($column);
        else
            $col = new MySQLColumn($column);

        $col->append(" $type $attrs", $colname);

        $this->columns[] = $col;

        return $col;
    }

    /**
     * Render a length for SQL data types in query.
     *
     * @param $length
     * @return string
     */
    public function renderLength($length): string
    {
        return $length === 0 ? '' : "($length)";
    }

    /**
     * Render data type.
     *
     * @param string $type
     * @param int $length
     * @return string
     */
    public function renderType(string $type, int $length = 0): string
    {
        return "$type" . $this->renderLength($length);
    }

    /**
     * Add an integer column.
     *
     * @param string $column
     * @param int $length
     * @return Column
     */
    public function int(string $column, int $length = 0): Column
    {
        return $this->add($this->renderType("INTEGER", $length), $column);
    }

    /**
     * Add a bigint column.
     *
     * @param string $column
     * @param int $length
     * @return Column
     */
    public function bigint(string $column, int $length = 0): Column
    {
        return $this->add($this->renderType("BIGINT", $length), $column);
    }

    /**
     * Add foreign ID and keys from the given models.
     *
     * @param array $models
     * @param string $postfix
     */
    public function foreignIdsFor(array $models, string $postfix = '_id')
    {
        foreach ($models as $k => $model) {
            /**
             * @var Model $m
             */
            $m = new $model();
            $models[$k] = $m;
            $col = preg_replace('/s$/', '', $m->table) . $postfix;
            $this->int($col)->notNull();
        }

        foreach ($models as $model) {
            $this->foreignKey(preg_replace('/s$/', '', $model->table) . $postfix, $model->table, $model->primaryColumn);
        }
    }

    /**
     * Add a VARCHAR column.
     *
     * @param string $column
     * @param int $length
     * @return Column
     */
    public function string(string $column, int $length = 0): Column
    {
        return $this->add($this->renderType("VARCHAR", $length === 0 ? 255 : $length), $column);
    }

    /**
     * Add a text column.
     *
     * @param string $column
     * @return Column
     */
    public function text(string $column): Column
    {
        return $this->add($this->renderType("TEXT"), $column);
    }

    /**
     * Add a TIMESTAMP column.
     *
     * @param string $column
     * @return Column
     */
    public function timestamp(string $column): Column
    {
        return $this->add($this->renderType("TIMESTAMP"), $column);
    }

    /**
     * Add a DATE column.
     *
     * @param string $column
     * @return Column
     */
    public function date(string $column): Column
    {
        return $this->add($this->renderType("DATE"), $column);
    }
    /**
     * Add a TIME column.
     *
     * @param string $column
     * @return Column
     */
    public function time(string $column): Column
    {
        return $this->add($this->renderType("TIME"), $column);
    }
    /**
     * Add a DATETIME column.
     *
     * @param string $column
     * @return Column
     */
    public function datetime(string $column): Column
    {
        return $this->add($this->renderType("DATETIME"), $column);
    }

    /**
     * Add a foreign key.
     *
     * @param string $column
     * @param string $reference_table
     * @param string $reference_column
     * @return Column
     */
    public function foreignKey(string $column, string $reference_table, string $reference_column): Column
    {
        return $this->add("FOREIGN KEY ($column) REFERENCES {$reference_table}($reference_column)", '', '', false);
    }

    /**
     * Add timestamp columns: `created_at`, `updated_at`.
     *
     * @param string $column
     */
    public function timestamps(string $column = '')
    {
        $cols = ['created_at', 'updated_at'];

        if ($column == 'created_at')
            $cols = ["created_at"];
        elseif ($column == 'updated_at')
            $cols = ["updated_at"];

        foreach ($cols as $col) {
            $this->datetime($col);
        }
    }
}
