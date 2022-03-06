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

namespace OSN\Framework\Core;


use App\Models\User;
use Error;
use Exception;
use JsonSerializable;
use OSN\Framework\Database\Query;
use OSN\Framework\Database\QueryBuilderTrait;
use OSN\Framework\Database\Table;
use OSN\Framework\Database\TableQueryTrait;
use OSN\Framework\Exceptions\ModelException;
use OSN\Framework\Exceptions\PropertyNotFoundException;
use OSN\Framework\ORM\ORMBaseTrait;
use OSN\Framework\ORM\Relationship;
use PDO;


/**
 * @method select(array|string $data = [])
 * @method patch()
 * @method insert()
 * @method truncate()
 */
abstract class Model implements JsonSerializable
{
    use ORMBaseTrait;

    protected array $data = [];
    protected array $fillable = [];
    protected array $guarded = [];

    public ?string $table = null;
    public self $pivot;
    public string $primaryColumn = 'id';
    public Table $_table;
    protected Database $db;
    protected bool $shortFetchRelations = true;

    public function __construct(?array $data = null)
    {
        if ($data != null)
            $this->load($data);

        $this->db = db();

        if($this->table === null) {
            $array = explode('\\', get_class($this));
            $this->table = strtolower(end($array)) . 's';
        }

        $this->guarded[] = $this->primaryColumn;
        $this->_table = new Table($this->table, $this->primaryColumn, static::class);
    }

    protected function db(): Database
    {
        return $this->db;
    }

    public function pivot()
    {
        return $this->pivot;
    }

    public function get($key = true)
    {
        if ($key === true)
            return $this->data;

        return $this->data[$key] ?? false;
    }

    /**
     * @throws ModelException
     */
    public function load(array $data)
    {
        foreach ($data as $key => $value) {
            if (!$this->isFillable($key))
                throw new ModelException("The field '" . static::class . "::$key' is not fillable");

            $this->data[$key] = $value;
        }
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function __get($name)
    {
        if (method_exists(static::class, $name) && !method_exists(self::class, $name) && $this->shortFetchRelations) {
            $data = call_user_func([$this, $name]);
            if ($data instanceof Relationship)
                return $data->get();
        }

        $data = $this->get($name);

        if ($data === false) {
            throw new PropertyNotFoundException('Cannot find the specified property', $name);
        }

        return $data;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function isFillable($field): bool
    {
        if (in_array($field, $this->fillable) && !in_array($field, $this->guarded)) {
            return true;
        }

        return false;
    }

    public function isGuarded($field): bool
    {
        if (in_array($field, $this->guarded) && !in_array($field, $this->fillable)) {
            return true;
        }

        return false;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * @param $primaryValue
     * @return self|null
     */
    public static function find($primaryValue): ?static
    {
        $model = new static();

        $data = $model->_table->select()->where($model->_table->primaryKey, $primaryValue)->get()->hasGet(0);

        if ($data === null)
            return null;

        foreach ($data as $k => $item) {
            $model->{$k} = $item;
        }

        return $model;
    }

    /**
     * @throws ModelException
     */
    public static function all(): Collection
    {
        $models = collection();

        try {
            $tmp = new static();
            $data = $tmp->db->queryFetch("SELECT * FROM " . $tmp->table);

            foreach ($data as $datum) {
                $model = new static();

                foreach ($datum as $field => $value) {
                    $model->$field = $value;
                }

                $models->push($model);
            }

            return $models;
        }
        catch (Exception $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }


    /*
     * The CRUD Methods (MASS ASSIGNMENT).
     */

    /**
     * @throws ModelException
     * @return Model|Collection
     */
    public static function create(array $data)
    {
        $model = new static();
        $model->load($data);
        $model->insert()->execute();

        return $model;
    }

    /**
     * @throws ModelException
     */
    public static function update(array $data)
    {
        $model = new static();
        $primaryValue = $data[$model->primaryColumn] ?? false;
        $patchedData = $data;

        if ($primaryValue !== false) {
            unset($patchedData[$model->primaryColumn]);
        }

        $model->load($patchedData);
        $model->{$model->primaryColumn} = $primaryValue;
        $model->patch()->execute();

        return static::find($primaryValue);
    }

    /**
     * @throws ModelException
     */
    public static function destroy(int $primaryValue): self
    {
        $model = new static();
        $data = static::find($primaryValue);
        $model->_table->delete()->where($model->primaryColumn, $primaryValue)->execute();

        return $data;
    }

    protected static function isCUD($name): bool
    {
        if ($name === 'insert' || $name === 'patch' || $name === 'delete')
            return true;

        return false;
    }

    public function save()
    {
        return $this->insert()->execute();
    }

    public function push()
    {
        return $this->patch()->execute();
    }

    public function __call($name, $args)
    {
        if ($name === 'insert') {
            return call_user_func_array([$this->_table, 'insert'], array_merge([$this->data], $args));
        }

        if ($name === 'patch') {
            return call_user_func_array([$this->_table, 'patch'], array_merge([$this->data], $args))->where($this->primaryColumn, $this->{$this->primaryColumn});
        }

        if ($name === 'delete') {
            return call_user_func_array([$this->_table, 'delete'], [])->where($this->primaryColumn, $this->{$this->primaryColumn});
        }

        return call_user_func_array([$this->_table, $name], $args);
    }

    public static function __callStatic($name, $args)
    {
        $obj = new static();
        return call_user_func_array([$obj->_table, $name], $args);
    }
}
