<?php


namespace OSN\Framework\ORM\Relationships;


use OSN\Framework\Core\Model;
use OSN\Framework\Database\Query;

class HasOneThrough extends HasOne
{
    protected Model $bridge;

    public function __construct(Model $baseModel, Model $relationalModel, Model $bridge, bool $initParent = true)
    {
        $this->bridge = $bridge;
        parent::__construct($baseModel, $relationalModel, $initParent);
    }

    protected function makeQuery()
    {
        $subQuery = new Query();
        $data = $subQuery
            ->select($this->bridge->table, [$this->bridge->primaryColumn])
            ->where($this->bridge->table . '.' . $this->tableToForeignColumn($this->baseModel->table), $this->baseModel->get($this->baseModel->primaryColumn));

        $data2 = $data->get();

        if ($data2->count() < 1) {
            return $data;
        }

        $bridge_id = $data2[0][$this->bridge->primaryColumn];

        return $this->query
            ->select($this->relationalModel->table)
            ->where($this->relationalModel->table . '.' . $this->tableToForeignColumn($this->bridge->table), $bridge_id);
    }
}
