<?php


namespace OSN\Framework\ORM;


use OSN\Framework\Core\Collection;

trait ManyToManyTrait
{
    public function get()
    {
        /** @var Collection $data */
        /** @var Collection $data2 */
        $data = parent::baseGet();

        $data2 = collection();
        $class = get_class($this->relationalModel);

        $data->each(function ($value, $key) use ($class, $data2) {
            $model = new $class;

            $key1 = $this->tableToForeignColumn($this->relationalModel->table);
            $key2 = $this->tableToForeignColumn($this->baseModel->table);
            $model->pivot = new Pivot($key1, $key2);

            foreach ($value as $k => $datum) {
                if ($k === $key1 || $k === $key2) {
                    $model->pivot->$k = $datum;
                    continue;
                }

                $model->{$k} = $datum;
            }

            $data2->set($key, $model);
        });

        return $data2;
    }
}
