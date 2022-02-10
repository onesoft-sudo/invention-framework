<?php


namespace OSN\Framework\ORM\Relationships\Polymorphic;


use OSN\Framework\Core\Model;
use OSN\Framework\Database\Query;
use OSN\Framework\ORM\PolymorphicRelationship;

class MorphTo extends PolymorphicRelationship
{
    public function __construct(Model $baseModel, string $keyword = "", bool $initParent = true)
    {
        parent::__construct($baseModel, null, $keyword, $initParent);
    }

    /**
     * @return mixed
     */
    protected function makeQuery()
    {
        $subquery = (new Query())
            ->select($this->baseModel->table)
            ->where($this->baseModel->table . '.' . $this->baseModel->primaryColumn, $this->baseModel->get($this->baseModel->primaryColumn));

        $tmp = $subquery->get();

        $keyword = preg_replace('/s$/', '', $this->baseModel->table) . 'able';
        $this->keyword = $keyword;

        if ($tmp === null || $tmp->count() < 1) {
            return $subquery;
        }

        $this->relationalModel = new $tmp[0][$keyword . '_type'];
        $this->relationalModelClass = get_class($this->relationalModel);

        return $this->query
            ->select($this->relationalModel->table)
            ->where($this->relationalModel->table . '.' . $this->relationalModel->primaryColumn, $this->baseModel->get($keyword . '_id'));
    }

    public function get()
    {
        $data = parent::get();
        return $data->hasGet(0);
    }
}
