<?php


namespace OSN\Framework\ORM;


use OSN\Framework\Core\Database;
use \OSN\Framework\Contracts\Query as QueryInterface;

class Query implements QueryInterface
{
    protected bool $select = false;
    protected bool $insert = false;
    protected bool $update = false;
    protected bool $delete = false;

    protected string $start = '';
    protected array $columns = [];
    protected array $columnValues = [];
    protected string $table = '';
    protected string $where = '';
    protected string $orderBy = '';
    protected bool $orderByDesc = false;
    protected string $limit = '';
    protected string $limit_offset = '';
    protected string $groupBy = '';
    protected string $having = '';

    protected string $raw = '';
    protected bool $rawQuery = false;

    public function __construct(protected Database $db, ?string $table = null)
    {
        if ($table !== null) {
            $this->table($table);
        }
    }

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function selectRaw(string $start): static
    {
        $this->select = true;
        $this->start = $start;
        $this->rawQuery = true;
        return $this;
    }

    public function insertRaw(string $start): static
    {
        $this->insert = true;
        $this->start = $start;
        $this->rawQuery = true;
        return $this;
    }

    public function updateRaw(string $start): static
    {
        $this->update = true;
        $this->start = $start;
        $this->rawQuery = true;
        return $this;
    }

    public function deleteRaw(string $start): static
    {
        $this->delete = true;
        $this->start = $start;
        $this->rawQuery = true;
        return $this;
    }

    public function select(array $columns = [], bool $distinct = false)
    {
        $this->columns = $columns;
        $this->select = true;
        return $this;
    }

    public function insert(array $data = [])
    {
        $this->columns = array_keys($data);
        $this->columnValues = array_values($data);
        $this->insert = true;
        return $this;
    }

    public function update(array $data = [])
    {
        $this->columns = array_keys($data);
        $this->columnValues = array_values($data);
        $this->update = true;
        return $this;
    }

    public function whereRaw(string $start): static
    {
        $this->where = $start;
        return $this;
    }

    public function where(string $column, string $valueOrOperator, ?string $value = null): static
    {
        $and = '';

        if ($this->where !== '')
            $and = ' AND ';

        if ($value) {
            return $this->whereRaw($this->where . " $and {$column} {$valueOrOperator} ?")->addValue($value);
        }

        return $this->whereRaw($this->where . " $and {$column} = ?")->addValue($valueOrOperator);
    }

    public function orWhere(string $column, string $valueOrOperator, ?string $value = null): static
    {
        if ($value) {
            return $this->whereRaw($this->where . " OR {$column} {$valueOrOperator} ?")->addValue($value);
        }

        return $this->whereRaw($this->where . " OR {$column} = ?")->addValue($valueOrOperator);
    }

    public function andWhere(string $column, string $valueOrOperator, ?string $value = null): static
    {
        if ($value) {
            return $this->whereRaw($this->where . " AND {$column} {$valueOrOperator} ?")->addValue($value);
        }

        return $this->whereRaw($this->where . " AND {$column} = ?")->addValue($valueOrOperator);
    }

    public function getValues()
    {
        return $this->columnValues;
    }

    public function orderBy(string $column, bool $desc = false): static
    {
        $this->orderBy = $column;
        $this->orderByDesc = $desc;
        return $this;
    }

    public function generateQuery()
    {
        if ($this->rawQuery) {
            $query = str_replace('{{table}}', $this->table, $this->start);
        }
        else {
            $query = '';

            if ($this->insert) {
                $columns = implode(', ', $this->columns);
                $qmarks = '';

                foreach ($this->columns as $column) {
                    $qmarks .= '?, ';
                }

                $qmarks = substr($qmarks, 0, strlen($qmarks) - 2);

                $query = "INSERT INTO {$this->table}(" . $columns . ") VALUES({$qmarks})";
            }

            if ($this->select) {
                $col = '*';

                if (!empty($this->columns)) {
                    $col = implode(', ', $this->columns);
                }

                $query = "SELECT {$col} FROM {$this->table}";
            }

            if ($this->update) {
                $columns = implode(' = ?, ', $this->columns) . ' = ?';
                $query = "UPDATE {$this->table} SET {$columns}";
            }

            if ($this->delete) {
                $query = "DELETE FROM ";
            }
        }

        if (!$this->insert && $this->where) {
            $query .= " WHERE {$this->where}";
        }

        if ($this->orderBy) {
            $query .= " ORDER BY {$this->orderBy}" . ($this->orderByDesc ? ' DESC' : '');
        }

        if ($this->limit) {
            $query .= " LIMIT " . ($this->limit_offset !== '' ? "{$this->limit_offset}, {$this->limit}" : $this->limit);
        }

        if ($this->groupBy) {
            $query .= " GROUP BY {$this->groupBy}";
        }

        if ($this->having) {
            $query .= " HAVING {$this->having}";
        }

        $this->raw = $query;

        return $query;
    }

    public function addValue(mixed $value)
    {
        $this->columnValues[] = $value;
        return $this;
    }

    public function prepare(string $sql): bool|\PDOStatement
    {
        return $this->db->prepare($sql);
    }

    public function getQuery()
    {
        return $this->generateQuery();
    }

    public function exec(): bool
    {
        return $this->prepare($this->generateQuery())->execute($this->columnValues);
    }

    public function get(bool $cast = true): array
    {
        $stmt = $this->prepare($this->generateQuery());

        if ($stmt->execute($this->columnValues)) {
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!$cast) {
                return $data;
            }
            else {
                $modelClassName = "\\App\\Models\\" . ucfirst(preg_replace('/s$/', '', $this->table));

                if (!class_exists($modelClassName)) {
                    return $data;
                }

                return array_map(function ($row) use ($modelClassName) {
                    return new $modelClassName($row);
                }, $data);
            }
        }
        else {
            throw new \RuntimeException('Database query error: ' . $stmt->errorInfo());
        }
    }
}