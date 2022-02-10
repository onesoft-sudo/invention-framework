<?php

namespace OSN\Framework\Console;

use Closure;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Migration;
use PDO;

class Migrations
{
    protected string $path;
    protected array $migrations = [];
    protected Database $db;

    public function __construct()
    {
        $this->path = App::$app->config["root_dir"] . "/database/migrations/";
        $this->migrations = scandir($this->path);
        $this->db = App::db();
    }

    public function applyAll(Closure $callback1 = null, Closure $callback2 = null, Closure $callback3 = null)
    {
        asort($this->migrations);
        foreach ($this->migrations as $migrationFile) {
            if (is_dir($migrationFile))
                continue;

            $migrationClass = explode(".", $migrationFile)[0];

            if ($callback1 !== null)
                $callback1($migrationClass);

            include_once $this->path . $migrationFile;

            /** @var Migration $migration */
            $migration = new $migrationClass();

            if($migration->up($this->db) === false) {
                if ($callback3 !== null)
                    $callback3($migrationClass);

                continue;
            }

            if ($callback2 !== null)
                $callback2($migrationClass);
        }
    }

    public function reset(Closure $callback1 = null, Closure $callback2 = null, Closure $callback3 = null)
    {
        rsort($this->migrations);
        foreach ($this->migrations as $migrationFile) {
            if (is_dir($migrationFile))
                continue;

            $migrationClass = explode(".", $migrationFile)[0];

            if ($callback1 !== null)
                $callback1($migrationClass);

            include_once $this->path . $migrationFile;

            $migration = new $migrationClass();

            if($migration->down($this->db) === false) {
                if ($callback3 !== null)
                    $callback3($migrationClass);

                continue;
            }

            if ($callback2 !== null)
                $callback2($migrationClass);
        }
    }
}
