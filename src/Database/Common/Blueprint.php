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

abstract class Blueprint
{
    protected string $table;
    protected string $sqlStart = '';
    protected string $sqlEnd = '';

    /**
     * @var SQLiteColumn[]|MySQLColumn[]
     */
    protected array $columns = [];

    /**
     * Blueprint constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @param string $sqlStart
     */
    public function setSQLStart(string $sqlStart): void
    {
        $this->sqlStart = $sqlStart;
    }

    /**
     * @param string $sqlEnd
     */
    public function setSQLEnd(string $sqlEnd): void
    {
        $this->sqlEnd = $sqlEnd;
    }

    public function __toString()
    {
        return $this->getSQL();
    }

    public function __invoke(): string
    {
        return $this->getSQL();
    }

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

    public function getColumns(): array
    {
        return $this->columns;
    }

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

    public function renderLength($length): string
    {
        return $length === 0 ? '' : "($length)";
    }

    public function renderType(string $type, int $length = 0): string
    {
        return "$type" . $this->renderLength($length);
    }

    public function int(string $column, int $length = 0): Column
    {
        return $this->add($this->renderType("INTEGER", $length), $column);
    }

    public function bigint(string $column, int $length = 0): Column
    {
        return $this->add($this->renderType("BIGINT", $length), $column);
    }

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

    public function string(string $column, int $length = 0): Column
    {
        return $this->add($this->renderType("VARCHAR", $length === 0 ? 255 : $length), $column);
    }

    public function text(string $column): Column
    {
        return $this->add($this->renderType("TEXT"), $column);
    }

    public function timestamp(string $column): Column
    {
        return $this->add($this->renderType("TIMESTAMP"), $column);
    }

    public function date(string $column): Column
    {
        return $this->add($this->renderType("DATE"), $column);
    }

    public function time(string $column): Column
    {
        return $this->add($this->renderType("TIME"), $column);
    }

    public function datetime(string $column): Column
    {
        return $this->add($this->renderType("DATETIME"), $column);
    }

    public function foreignKey(string $column, string $reference_table, string $reference_column): Column
    {
        return $this->add("FOREIGN KEY ($column) REFERENCES {$reference_table}($reference_column)", '', '', false);
    }

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
