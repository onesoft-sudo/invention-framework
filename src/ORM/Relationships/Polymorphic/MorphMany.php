<?php


namespace OSN\Framework\ORM\Relationships\Polymorphic;


use OSN\Framework\ORM\PolymorphicRelationship;

class MorphMany extends PolymorphicRelationship
{
    /**
     * @return mixed
     */
    protected function makeQuery()
    {
        return $this->query
            ->select($this->relationalModel->table)
            ->where($this->relationalModel->table . '.' . $this->keyword . '_id', $this->baseModel->get($this->baseModel->primaryColumn))
            ->andWhere($this->relationalModel->table . '.' . $this->keyword . '_type', get_class($this->baseModel));
    }
}
