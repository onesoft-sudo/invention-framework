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

/**
 * The database query class.
 *
 * @package OSN\Framework\Database
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Query
{
    use QueryBuilderTrait;

    /**
     * Query constructor.
     *
     * @param string $currentTable
     * @param string $model
     */
    public function __construct(string $currentTable = '', string $model = '')
    {
        $this->db = db();
        $this->currentTable = $currentTable;
        $this->model = $model;
    }

    /**
     * Convert the object to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getQuery();
    }

    /**
     * Run a raw query.
     *
     * @param string $sql
     * @return bool|\PDOStatement
     */
    public function raw(string $sql): bool|\PDOStatement
    {
        return $this->db->query($sql);
    }

    /**
     * Run a raw query and fetch result.
     *
     * @param string $sql
     * @param int $flags
     * @return array
     */
    public function rawFetch(string $sql, int $flags = \PDO::FETCH_ASSOC): array
    {
        return $this->raw($sql)->fetchAll($flags);
    }
}
