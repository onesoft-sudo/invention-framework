<?php


namespace OSN\Framework\Database;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Model;
use OSN\Framework\Exceptions\FactoryLimitException;

/**
 * @property Factory model
 */
abstract class Factory
{
    protected Generator $faker;
    protected int $count = 1;
    protected int $maxCount = 100;

    abstract protected function define(): array;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

    /**
     * @throws FactoryLimitException
     */
    public function make()
    {
        $array = [];

        for ($i = 1; $i <= $this->maxCount && $i <= $this->count; $i++) {
            if ($i >= $this->maxCount) {
                throw new FactoryLimitException();
            }

            $model = new $this->model();
            $definition = $this->define();

            foreach ($definition as $field => $value) {
                $model->$field = $value;
            }

            $array[] = $model;
        }

        return collection($array);
    }

    public function count(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @throws FactoryLimitException
     */
    public function create(): Collection
    {
        $models = $this->make();

        $models->each(function ($model) {
            /** @var Model $model */
            $model->insert();
        });

        return $models;
    }

    public static function newInstance(): self
    {
        return new static();
    }

    public static function new(): Model
    {
        return self::newInstance()->count(1)->make()->get(0);
    }
}
