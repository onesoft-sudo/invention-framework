<?php


namespace OSN\Framework\ORM;


use OSN\Framework\Core\Model;

class Pivot extends Model
{
    public string $foreignColumn;
    protected array $guarded;

    public function __construct($foreignColumn1, $foreignColumn2, ?array $data = null)
    {
        $this->primaryColumn = $foreignColumn1;
        $this->foreignColumn = $foreignColumn2;

        $this->guarded = [$foreignColumn1, $foreignColumn2];

        parent::__construct($data);
    }
}
