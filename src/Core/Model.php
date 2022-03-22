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
use ArrayAccess;
use ArrayIterator;
use Countable;
use Error;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use OSN\Framework\Database\Query;
use OSN\Framework\Database\QueryBuilderTrait;
use OSN\Framework\Database\Table;
use OSN\Framework\Database\TableQueryTrait;
use OSN\Framework\Exceptions\ModelException;
use OSN\Framework\Exceptions\PropertyNotFoundException;
use OSN\Framework\Foundation\Bootable;
use OSN\Framework\ORM\ORMBaseTrait;
use OSN\Framework\ORM\Relationship;
use OSN\Framework\Security\Policy;
use PDO;


/**
 * The base model class.
 *
 * @method select(array|string $data = [])
 * @method patch()
 * @method insert()
 * @method truncate()
 */
abstract class Model implements JsonSerializable, IteratorAggregate, Countable, ArrayAccess
{
    use ORMBaseTrait, Bootable;

    /**
     * The raw data.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * The list of fillable properties (mass assignment).
     *
     * @var array
     */
    protected array $fillable = [];

    /**
     * The list of guarded properties (mass assignment).
     *
     * @var array
     */
    protected array $guarded = [];

    /**
     * The corresponding policy.
     *
     * @var Policy|null
     */
    protected ?Policy $policy;

    /**
     * The corresponding table name.
     *
     * @var string|null
     */
    public ?string $table = null;

    /**
     * The pivot model instance.
     *
     * @var self
     */
    public self $pivot;

    /**
     * The column that has a primary key constraint.
     *
     * @var string
     */
    public string $primaryColumn = 'id';

    /**
     * The table object.
     *
     * @var Table
     */
    public Table $_table;

    /**
     * The database instance.
     *
     * @var Database
     */
    protected Database $db;

    /**
     * Determine that the model should automatically resolve relations
     * while trying to get non-existing properties.
     *
     * @var bool
     */
    protected bool $shortFetchRelations = true;

    /**
     * Model constructor.
     *
     * @param array|null $data
     * @throws ModelException
     */
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
        $this->setPolicy();
        $this->bootUp();
    }

    /**
     * Set an appropriate policy instance.
     *
     * @return void
     */
    protected function setPolicy()
    {
        $policy = config('namespaces')['policies'] . "\\" . get_base_class(static::class) . "Policy";
        $this->policy = class_exists($policy) ? new $policy(auth()->user(), $this) : null;
    }

    /**
     * Get the database instance.
     *
     * @return Database
     */
    protected function db(): Database
    {
        return $this->db;
    }

    /**
     * Get the pivot model.
     *
     * @return Model
     */
    public function pivot()
    {
        return $this->pivot;
    }

    /**
     * Get a field value.
     *
     * @param bool $key
     * @return array|false|mixed
     */
    public function get($key = true)
    {
        if ($key === true)
            return $this->data;

        return $this->data[$key] ?? false;
    }

    /**
     * Load data on the model.
     *
     * @param array $data
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
     * Get a field value while trying to get non-existing properties.
     *
     * @param mixed $name
     * @return array|mixed|Collection
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

    /**
     * Set values of the data fields.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Determine if a field is fillable.
     *
     * @param $field
     * @return bool
     */
    public function isFillable($field): bool
    {
        if (in_array($field, $this->fillable) && !in_array($field, $this->guarded)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a field is guarded.
     *
     * @param $field
     * @return bool
     */
    public function isGuarded($field): bool
    {
        if (in_array($field, $this->guarded) && !in_array($field, $this->fillable)) {
            return true;
        }

        return false;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * Find rows by primary key value.
     *
     * @param $primaryValue
     * @return self|null
     * @throws \OSN\Framework\Exceptions\CollectionException
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
     * Get all rows.
     *
     * @return Collection
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
     * Create a record.
     *
     * @param array $data
     * @return Model
     * @throws ModelException
     */
    public static function create(array $data)
    {
        $model = new static();
        $model->load($data);
        $model->insert()->execute();

        return $model;
    }

    /**
     * Update a record.
     *
     * @param array $data
     * @return Model|static|null
     * @throws ModelException
     * @throws \OSN\Framework\Exceptions\CollectionException
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
     * Delete a record by primary key values.
     *
     * @param int $primaryValue
     * @return static
     * @throws \OSN\Framework\Exceptions\CollectionException
     */
    public static function destroy(int $primaryValue): self
    {
        $model = new static();
        $data = static::find($primaryValue);
        $model->_table->delete()->where($model->primaryColumn, $primaryValue)->execute();

        return $data;
    }

    /**
     * Determine if the given method name is one if Create-Update-Delete.
     * This function is called by the __callStatic() method.
     *
     * @param $name
     * @return bool
     */
    protected static function isCUD($name): bool
    {
        if ($name === 'insert' || $name === 'patch' || $name === 'delete')
            return true;

        return false;
    }

    /**
     * Insert data to the DB.
     *
     * @return mixed
     */
    public function save()
    {
        return $this->insert()->execute();
    }

    /**
     * Update changes.
     *
     * @return mixed
     */
    public function push()
    {
        return $this->patch()->execute();
    }

    /**
     * Return a Query instance.
     *
     * @param string $sql
     * @return bool|\PDOStatement|Query|Table
     */
    public static function query(string $sql = ''): bool|\PDOStatement|Query|Table
    {
        $instance = new static();

        if ($sql !== '') {
            return (new Query())->raw(str_replace('{table}', $instance->table, $sql));
        }

        return $instance->_table;
    }

    /**
     * Determine if the user can do an action.
     *
     * @param string $action
     * @return bool
     */
    public function can(string $action): bool
    {
        return $this->policy->can($action);
    }

    /**
     * Determine if the user cannot do an action.
     *
     * @param string $action
     * @return bool
     */
    public function cannot(string $action): bool
    {
        return !$this->can($action);
    }

    /**
     * Retrieve an external iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Get the count of data fields.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Determine if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * Get the value of an offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->data[$offset];
    }

    /**
     * Set the value of an offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * Unset an offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Resolve non-existing method calls using the table instance.
     *
     * @param $name
     * @param $args
     * @return false|mixed
     */
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

    /**
     * Resolve non-existing static method calls using the model instance.
     *
     * @param $name
     * @param $args
     * @return false|mixed
     */
    public static function __callStatic($name, $args)
    {
        $obj = new static();
        return call_user_func_array([$obj->_table, $name], $args);
    }
}
