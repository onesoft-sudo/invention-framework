<?php


namespace OSN\Framework\Database\SQLite;

use \OSN\Framework\Database\Common\Blueprint as CommonBlueprint;
use \OSN\Framework\Database\Common\Column as CommonColumn;

class Blueprint extends CommonBlueprint
{
    public function id(string $column = 'id'): CommonColumn
    {
        $col = $this->int($column);
        $col->autoIncrement();
        $this->primaryKey($column);
        return $col;
    }

    public function unsignedBigInt(string $column)
    {
        return $this->add("$column UNSIGNED BIG INT", '', '', false);
    }

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
