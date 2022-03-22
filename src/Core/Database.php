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

namespace OSN\Framework\Core;


use OSN\Framework\Database\DatabaseVendors;
use OSN\Framework\Database\Table;
use PDO;

/**
 * The database connection manager. It uses PDO internally to
 * interact with the database.
 *
 * @package OSN\Framework\Core
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Database
{
    /**
     * The PDO instance.
     *
     * @var PDO|null
     */
    public ?PDO $pdo;

    /**
     * The DSN string.
     *
     * @var string
     */
    public string $dsn;

    /**
     * The database name.
     *
     * @var string
     */
    public string $dbname;

    /**
     * The environment variables.
     *
     * @var array
     */
    public array $env;

    /**
     * Database constructor.
     *
     * @param array $env
     */
    public function __construct(array $env)
    {
        $this->env = $env;

        if (isset($env['DB_AUTO_CONNECT']) && $env['DB_AUTO_CONNECT'] === false)  {
            return;
        }

        $vendor = $this->getVendor();
        $dsn = $vendor . ":";

        if ($vendor === 'mariadb')
            $dsn = 'mysql:';

        $this->init($env, $vendor, $dsn);
    }

    /**
     * Initialize the PDO instance.
     *
     * @param $env
     * @param $vendor
     * @param $dsn
     * @todo Remove magic strings
     */
    public function init($env, $vendor, $dsn)
    {
        if ($vendor === 'mysql' || $vendor === 'mariadb') {
            $dsn .= 'host=' . $env["DB_HOST"] . ';port=' . $env["DB_PORT"] . ';dbname=' . $env["DB_NAME"];
            $this->pdo = new PDO($dsn, $env['DB_USER'], $env["DB_PASSWORD"]);
        }
        elseif ($vendor === 'sqlite') {
            $dsn .= $env["DB_NAME"];
            $this->pdo = new PDO($dsn);
        }

        $this->dsn = $dsn;
        $this->dbname = $env["DB_NAME"];
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Get vendor names.
     *
     * @return string|null
     */
    public function getVendor(): ?string
    {
        if (in_array($this->env['DB_VENDOR'], DatabaseVendors::$vendors)) {
            return $this->env['DB_VENDOR'];
        }

        return null;
    }

    /**
     * Choose one of multiple queries according to the database vendor.
     *
     * @param array $queries
     * @return mixed
     */
    public function chooseQuery(array $queries)
    {
        if (array_key_exists(DatabaseVendors::$vendors['mysql'], $queries)) {
            if (array_key_exists(DatabaseVendors::$vendors['mariadb'], $queries)) {
                return $queries[DatabaseVendors::$vendors['mariadb']];
            }
        }

        foreach ($queries as $vendor => $query) {
            if ($vendor == $this->getVendor()) {
                return $query;
            }
        }
    }

    /**
     * Run a raw SQL query.
     *
     * @param $sql
     * @return false|\PDOStatement
     */
    public function query($sql)
    {
        return $this->pdo->query($sql);
    }

    /**
     * An alias of PDO::exec().
     *
     * @param $sql
     * @return false|int
     */
    public function exec($sql)
    {
        return $this->pdo->exec($sql);
    }

    /**
     * Run raw query and fetch result.
     *
     * @param $sql
     * @param null $params
     * @return array
     */
    public function queryFetch($sql, $params = null): array
    {
        return $this->pdo->query($sql)->fetchAll($params !== null ? $params : PDO::FETCH_ASSOC);
    }

    /**
     * Prepare an SQL statement.
     *
     * @param $sql
     * @return false|\PDOStatement
     */
    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Get all tables list available in the current database.
     *
     * @return array
     */
    public function tables(): array
    {
        $tables = $this->queryFetch($this->chooseQuery([
            "mysql" => "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()",
            "sqlite" => "SELECT name FROM sqlite_master WHERE type = 'table'"
        ]));

        return $tables[0];
    }

    /**
     * Get a specific table.
     *
     * @param string $table
     * @return Table
     */
    public function table(string $table): Table
    {
        return new Table($table);
    }

    /**
     * Destruct the object and close PDO connection.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->pdo = null;
    }
}
