<?php


namespace OSN\Framework\Core;


use OSN\Framework\Database\DatabaseVendors;
use OSN\Framework\Database\Table;
use PDO;

class Database
{
    public ?PDO $pdo;
    public string $dsn;
    public string $dbname;
    public array $env;

    public function __construct($env)
    {
        $this->env = $env;

        $vendor = $this->getVendor();
        $dsn = $vendor . ":";

        if ($vendor === 'mariadb')
            $dsn = 'mysql:';

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
    }

    public function getVendor(): ?string
    {
        if (in_array($this->env['DB_VENDOR'], DatabaseVendors::$vendors)) {
            return $this->env['DB_VENDOR'];
        }

        return null;
    }

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

    public function query($sql)
    {
        return $this->pdo->query($sql);
    }

    public function exec($sql)
    {
        return $this->pdo->exec($sql);
    }

    public function queryFetch($sql, $params = null): array
    {
        return $this->pdo->query($sql)->fetchAll($params !== null ? $params : PDO::FETCH_ASSOC);
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    public function tables()
    {
        $tables = $this->queryFetch($this->chooseQuery([
            "mysql" => "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()",
            "sqlite" => "SELECT name FROM sqlite_master WHERE type = 'table'"
        ]));

        return $tables[0];
    }

    public function table(string $table): Table
    {
        return new Table($table);
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}
