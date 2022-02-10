<?php


namespace OSN\Framework\Database\MySQL;

use \OSN\Framework\Database\Common\Blueprint as CommonBlueprint;
use \OSN\Framework\Database\Common\Column as CommonColumn;

class Blueprint extends CommonBlueprint
{
    public function id(string $column = 'id'): CommonColumn
    {
        $col = $this->int($column);
        $col->notNull()->unique()->autoIncrement();
        $this->primaryKey($column);
        return $col;
    }

    public function unsignedBigInt(string $column)
    {
        return $this->add("$column BIGINT", '', 'UNSIGNED', false);
    }

    public function primaryKey(string $column): CommonColumn
    {
        return $this->add("PRIMARY KEY ($column", '', ')', false);
    }
}
