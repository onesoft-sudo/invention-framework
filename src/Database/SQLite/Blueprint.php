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

namespace OSN\Framework\Database\SQLite;

use \OSN\Framework\Database\Common\Blueprint as CommonBlueprint;
use \OSN\Framework\Database\Common\Column as CommonColumn;

/**
 * Blueprint class for using with SQLite.
 *
 * @package OSN\Framework\Database\SQLite
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Blueprint extends CommonBlueprint
{
    /**
     * Add a primary key column for using as IDs.
     *
     * @param string $column
     * @return CommonColumn
     */
    public function id(string $column = 'id'): CommonColumn
    {
        $col = $this->int($column);
        $col->autoIncrement();
        $this->primaryKey($column);
        return $col;
    }

    /**
     * Add an unsigned bigint column.
     *
     * @param string $column
     * @return CommonColumn
     */
    public function unsignedBigInt(string $column)
    {
        return $this->add("$column UNSIGNED BIG INT", '', '', false);
    }

    /**
     * Add a primary key.
     *
     * @param string $col
     * @return CommonColumn|null
     */
    public function primaryKey(string $col): ?CommonColumn
    {
        foreach ($this->columns as $column) {
            if ($column->column === $col) {
                $column->primaryKey();
                return $column;
            }
        }

        return null;
    }
}
