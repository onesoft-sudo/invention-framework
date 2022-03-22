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

/**
 * Common column class.
 *
 * @package OSN\Framework\Database\Common
 * @author Ar Rakin <rakinar2@gmail.com>
 */
abstract class Column
{
    /**
     * Partial SQL for the column.
     *
     * @var string
     */
    protected string $colSQL = '';

    /**
     * Name of the column.
     *
     * @var string
     */
    public string $column = '';

    /**
     * Column constructor.
     *
     * @param string $columnName
     */
    public function __construct(string $columnName)
    {
        $this->column = $columnName;
    }

    /**
     * Append SQL to the colSQL property.
     *
     * @param string $sql
     * @param bool $colname
     * @return $this
     */
    public function append(string $sql, bool $colname = true): self
    {
        if ($colname)
            $this->colSQL .= $this->column . ' ';

        $this->colSQL .= $sql;

        return $this;
    }

    /**
     * Get column name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->column;
    }

    /**
     * Append NOT NULL.
     *
     * @return $this
     */
    public function notNull(): self
    {
        return $this->append(" NOT NULL", false);
    }

    /**
     * Append UNSIGNED.
     *
     * @return $this
     */
    public function unsigned(): self
    {
        return $this->append(" UNSIGNED", false);
    }

    /**
     * Append UNIQUE.
     *
     * @return $this
     */
    public function unique(): self
    {
        return $this->append(" UNIQUE", false);
    }

    /**
     * Append DEFAULT.
     *
     * @param $default
     * @return $this
     */
    public function default($default): self
    {
        $defStr = is_string($default) ? '"' . addcslashes($default, '"') . '"' : $default;
        return $this->append(" DEFAULT $defStr", false);
    }

    /**
     * Invoke the object.
     *
     * @return string
     */
    public function __invoke(): string
    {
        return $this->colSQL;
    }
}
