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


use Closure;
use OSN\Framework\Console\App;
use OSN\Framework\Core\Database;
use OSN\Framework\Database\MySQL\Blueprint as MySQLBlueprint;
use OSN\Framework\Database\SQLite\Blueprint as SQLiteBlueprint;
use OSN\Framework\Facades\_String;

/**
 * Class Schema
 * @package App\Console
 * @todo Add createIfNotExists() method
 */
class Schema
{
    protected Database $db;

    /**
     * DatabaseSchema constructor.
     */
    public function __construct()
    {
        $this->db = app()->db;
    }

    public function blueprint($table)
    {
        if ($this->db->getVendor() === 'sqlite')
            $blueprint = new SQLiteBlueprint($table);
        else
            $blueprint = new MySQLBlueprint($table);

        return $blueprint;
    }

    /**
     * @param string $table
     * @param Closure $callback
     * @return MySQLBlueprint|SQLiteBlueprint
     * @todo Fix query
     */
    public function create(string $table, Closure $callback)
    {
        $blueprint = $this->blueprint($table);
        $blueprint->setSQLStart("CREATE TABLE {{ table }}(");
        $blueprint->setSQLEnd(")");
        call_user_func_array($callback, [$blueprint]);
        $this->exec($blueprint . '');
        return $blueprint;
    }

    public function add(string $table, Closure $callback)
    {
        $blueprint = $this->blueprint($table);
        $blueprint->setSQLStart("ALTER TABLE {{ table }} ADD");
        $blueprint->setSQLEnd(";");
        call_user_func_array($callback, [$blueprint]);
        $query = $blueprint . '';
        $query = str_replace(',', ";\n ALTER TABLE $table ADD", $query);

        $this->exec($query);
        return $blueprint;
    }

    public function modify(string $table, Closure $callback)
    {
        $blueprint = $this->blueprint($table);

        if ($this->db->getVendor() !== 'sqlite') {
            $blueprint->setSQLStart('ALTER TABLE {{ table }} MODIFY COLUMN');
            $blueprint->setSQLEnd(";");

            call_user_func_array($callback, [$blueprint]);

            $query = $blueprint . '';
            $query = str_replace(',', ";\n ALTER TABLE $table MODIFY COLUMN", $query);
        }
        else {
            /**
             * All data in the table will lost in this case!
             * Also, this will cause high CPU load.
             */
            $tbl_sql = $this->db->queryFetch('SELECT * FROM sqlite_master WHERE type = \'table\' AND tbl_name = "' . $table . '"')[0]['sql'];
            $sql = preg_replace("/CREATE( *)TABLE( *)$table( *)\(/", '', $tbl_sql);
            $sql = _String::removeMultipleSpaces(_String::strLastReplace($sql, ')', ''));

            $parts = array_map(fn($value) => explode(' ', trim($value)), explode(',', $sql));

            $blueprint->setSQLStart('');
            $blueprint->setSQLEnd("");

            call_user_func_array($callback, [$blueprint]);
            $customQuery = $blueprint . '';
            $customParts = array_map(fn($value) => explode(' ', trim($value)), explode(',', trim(_String::removeMultipleSpaces($customQuery))));

            foreach ($parts as $i => $part) {
                foreach ($customParts as $customPart) {
                    if ($part[0] == $customPart[0]) {
                        $parts[$i] = $customPart;
                    }
                }
            }

            $out = '';

            foreach ($parts as $part) {
                $out .= implode(' ', $part) . ",\n";
            }

            $out = "DROP TABLE $table ;\nCREATE TABLE $table(\n" . substr($out, 0, strlen($out) - 2) . "\n)";

            $this->exec($out);
            return $blueprint;
        }

        $this->exec($query);
        return $blueprint;
    }

    public function dropColumns(string $table, array $columns)
    {
        if ($this->db->getVendor() === 'sqlite') {
            /**
             * All data in the table will lost in this case!
             * Also, this will cause high CPU load.
             */
            $tbl_sql = $this->db->queryFetch('SELECT * FROM sqlite_master WHERE type = \'table\' AND tbl_name = "' . $table . '"')[0]['sql'];
            $sql = preg_replace("/CREATE( *)TABLE( *)$table( *)\(/", '', $tbl_sql);
            $sql = _String::removeMultipleSpaces(_String::strLastReplace($sql, ')', ''));

            $parts = array_map(fn($value) => explode(' ', trim($value)), explode(',', $sql));

            foreach ($parts as $i => $part) {
                foreach ($columns as $column) {
                    if ($part[0] === $column) {
                        unset($parts[$i]);
                    }
                }
            }

            $out = '';

            foreach ($parts as $part) {
                $out .= implode(' ', $part) . ",\n";
            }

            $out = "DROP TABLE $table ;\nCREATE TABLE $table(\n" . substr($out, 0, strlen($out) - 2) . "\n)";

            $this->exec($out);
        }
        else {
            $mariadb = $this->db->getVendor() === 'mariadb';
            foreach ($columns as $column) {
                $sql = "ALTER TABLE $table DROP ";

                if ($mariadb) {
                    $sql .= 'COLUMN ';
                }

                $this->exec($sql . $column);
            }
        }
    }

    public function createIndex(string $table, string $name, array $columns)
    {
        $columns = implode(', ', $columns);
        $this->db->query("CREATE INDEX $name ON $table($columns)");
    }

    public function drop(string $table): string
    {
        $query = "DROP TABLE $table;";
        $this->exec($query);
        return $query;
    }

    public function dropIfExists(string $table): string
    {
        $query = "DROP TABLE IF EXISTS $table";
        $this->exec($query);
        return $query;
    }

    public function dropIndex(string $table, string $index)
    {
        if ($this->db->getVendor() === 'sqlite')
            $this->exec("DROP INDEX $table.$index");
        else
            $this->exec("ALTER TABLE $table DROP INDEX $index");
    }

    public function exec(string $string)
    {
        return $this->db->exec($string);
    }
}
