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


use OSN\Framework\Database\Schema;
use OSN\Framework\Database\MySQL\Blueprint;
use OSN\Framework\Foundation\Bootable;

/**
 * The base Migration class.
 *
 * @package OSN\Framework\Core
 * @author Ar Rakin <rakinar2@gmail.com>
 */
abstract class Migration
{
    use Bootable;

    /**
     * The name of the migration.
     *
     * @var string
     */
    protected string $migrationName;

    /**
     * Determines if the framework should save this
     * migration stats into the database.
     *
     * @var bool
     */
    protected bool $entryLogging = true;

    /**
     * Change the database.
     *
     * @return mixed
     */
    abstract public function safeUp();

    /**
     * Revert the database.
     *
     * @return mixed
     */
    abstract public function safeDown();

    /**
     * Migration constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->migrationName = get_class($this);
        $this->bootUp();
    }

    /**
     * Save this migration as "Applied" into the database.
     *
     * @param $pdo
     * @return void
     */
    public function registerMigration($pdo)
    {
        $timestamp = date("Y-m-d H:i:s");

        $stmt = $pdo->prepare("INSERT INTO migrations(name, created_at) VALUES(:name, :created_at);");
        $stmt->execute(["name" => $this->migrationName, "created_at" => $timestamp]);
    }

    /**
     * Delete this migration logging from database.
     *
     * @param $pdo
     * @return void
     */
    public function unregisterMigration($pdo)
    {
        $stmt = $pdo->prepare("DELETE FROM migrations WHERE name = :name");
        $stmt->execute(["name" => $this->migrationName]);
    }

    /**
     * Determine if the current migration is applied.
     *
     * @param $db
     * @return bool
     * @todo Update code
     */
    public function isApplied($db): bool
    {
        $tables = $db->pdo->query($db->chooseQuery([
            'sqlite' => "SELECT name FROM sqlite_master WHERE type = 'table'",
            'mysql' => "SELECT TABLE_NAME AS name
                        FROM INFORMATION_SCHEMA.TABLES
                        WHERE TABLE_SCHEMA = '" . \OSN\Framework\Console\App::$app->db->dbname . "'"
        ]))->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($tables as $table) {
            if ($table["name"] == 'migrations') {
                $migrations = $db->pdo->query("SELECT * FROM migrations WHERE name = '{$this->migrationName}'")->fetchAll(\PDO::FETCH_ASSOC);

                if (count($migrations) > 0) {
                    return true;
                }

                break;
            }
        }

        return false;
    }

    /**
     * Run the migration.
     *
     * @param $db
     * @return bool
     */
    public function up($db): bool
    {
        if (!$this->isApplied($db)){
            $q = $this->safeUp();

            if ($this->entryLogging)
                $this->registerMigration($db->pdo);

            return true;
        }

        return false;
    }

    /**
     * Revert the migration.
     *
     * @param $db
     * @return bool
     */
    public function down($db): bool
    {
        if ($this->isApplied($db)) {
            if ($this->entryLogging)
                $this->unregisterMigration($db->pdo);

            $q = $this->safeDown();

            return true;
        }

        return false;
    }
}
