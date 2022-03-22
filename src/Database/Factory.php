<?php
/*
 * Copyright 2020-2022 OSN Software Foundation, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OSN\Framework\Database;

use Closure;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Model;
use OSN\Framework\Exceptions\FactoryLimitException;
use OSN\Framework\Foundation\Bootable;

/**
 * Database Factory.
 *
 * @package OSN\Framework\Database
 * @author Ar Rakin <rakinar2@gmail.com>
 * @property Factory $model
 */
abstract class Factory
{
    use Bootable;

    /**
     * The faker generator instance.
     *
     * @var Generator
     */
    protected Generator $faker;

    /**
     * Count of rows to create.
     *
     * @var int
     */
    protected int $count = 1;

    /**
     * Max row count. $this->count must not exceed this limit.
     *
     * @var int
     */
    protected int $maxCount = 100;

    /**
     * Different states.
     *
     * @var Closure[]
     */
    protected array $states = [];

    /**
     * Definition of the factory.
     *
     * @return array
     */
    abstract protected function define(): array;

    /**
     * Factory constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = FakerFactory::create();
        $this->bootUp();
    }

    /**
     * Make the rows without making any changes to the DB.
     *
     * @param bool $one
     * @return Model|Collection
     * @throws FactoryLimitException
     */
    public function make(bool $one = true): Model|Collection
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

    /**
     * Set the row count.
     *
     * @param int $count
     * @return $this
     */
    public function count(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Create the rows in the DB.
     *
     * @return Collection
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

    /**
     * Add a state.
     *
     * @param Closure $callback The state definition.
     * @return $this
     */
    public function state(Closure $callback): self
    {
        $this->states[] = $callback;
        return $this;
    }

    /**
     * Create a new instance of the factory.
     *
     * @return static
     */
    public static function newInstance(): self
    {
        return new static();
    }

    /**
     * Create a new model from the definition.
     *
     * @return Model
     * @throws FactoryLimitException
     * @throws \OSN\Framework\Exceptions\CollectionException
     */
    public static function new(): Model
    {
        return self::newInstance()->count(1)->make()->get(0);
    }
}
