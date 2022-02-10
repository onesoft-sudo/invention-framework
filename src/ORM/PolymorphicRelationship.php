<?php


namespace OSN\Framework\ORM;


use OSN\Framework\Core\Model;

abstract class PolymorphicRelationship extends DualRelationship
{
    protected string $keyword;

    public function __construct(Model $baseModel, ?Model $relationalModel, string $keyword = "", bool $initParent = true)
    {
        if ($keyword === '' && $relationalModel !== null) {
            $keyword = preg_replace('/s$/', '', $relationalModel->table) . 'able';
        }

        $this->keyword = $keyword;
        parent::__construct($baseModel, $relationalModel, $initParent);
    }
}
