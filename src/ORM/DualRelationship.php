<?php


namespace OSN\Framework\ORM;


use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Model;

abstract class DualRelationship extends Relationship
{
    protected ?Model $relationalModel = null;
    protected Model $baseModel;
    protected string $relationalModelClass;
    protected string $baseModelClass;

    public function __construct(Model $baseModel, ?Model $relationalModel, bool $initParent = true)
    {
        $this->baseModel = $baseModel;
        $this->relationalModel = $relationalModel;
        $this->baseModelClass = get_class($baseModel);

        if ($relationalModel !== null)
            $this->relationalModelClass = get_class($relationalModel);

        if ($initParent)
            parent::__construct();
    }

    public function baseGet()
    {
        return parent::get();
    }

    public function get()
    {
        /** @var Collection $data */
        /** @var Collection $data2 */
        $data = parent::get();

        $data2 = collection();
        $class = get_class($this->relationalModel);

        $data->each(function ($value, $key) use ($class, $data2) {
            $model = new $class;

            foreach ($value as $k => $datum) {
                $model->{$k} = $datum;
            }

            $data2->set($key, $model);
        });

        return $data2;
    }
}
