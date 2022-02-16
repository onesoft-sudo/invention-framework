<?php


namespace OSN\Framework\Database;

use Closure;
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

    /**
     * @var Closure[]
     */
    protected array $states = [];

    abstract protected function define(): array;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

    /**
     * @throws FactoryLimitException
     */
    public function make(bool $one = true)
    {
        $array = [];

        for ($i = 1; $i >= 0 && $i <= $this->maxCount && $i <= $this->count; $i++) {
            if ($i >= $this->maxCount) {
                throw new FactoryLimitException();
            }

            $model = new $this->model();
            $definition = $this->define();

            foreach ($this->states as $state) {
                $definition = array_merge($definition, call_user_func_array($state, [$definition]));
            }

            foreach ($definition as $field => $value) {
                $model->$field = $value;
            }

            $array[] = $model;
        }

        return $this->count == 1 && $one ? $array[0] : collection($array);
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
        $models = $this->make(false);

        $models->each(function ($model) {
            /** @var Model $model */
            $model->insert()->execute();
        });

        return $models;
    }

    public function state(Closure $callback): self
    {
        $this->states[] = $callback;
        return $this;
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
