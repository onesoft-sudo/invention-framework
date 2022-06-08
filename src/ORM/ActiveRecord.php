<?php


namespace OSN\Framework\ORM;


use OSN\Framework\Contracts\JSONObject;
use OSN\Framework\Exceptions\PropertyNotFoundException;
use OSN\Framework\Utils\JSONAble;

abstract class ActiveRecord implements JSONObject
{
    use JSONAble;

    protected string $table;
    protected Query $query;
    protected bool $existingRecord = false;

    protected array $data = [];
    protected bool $allowNonExistingProperties = true;
    protected string $primaryKey = 'id';

    public function __construct(array $data = [])
    {
        if (!isset($this->table)) {
            $this->setTableName($this->generateTableName());
        }

        $this->query = new Query(app()->db(), $this->table);

        if (count($data) > 0) {
            $this->load($data);
        }
    }

    public function setTableName(string $name): static
    {
        $this->table = $name;
        return $this;
    }

    public function load(array $data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function selectRaw(string $start)
    {
        return $this->query->selectRaw($start);
    }

    public function insertRaw(string $start)
    {
        return $this->query->insertRaw($start);
    }

    public function updateRaw(string $start)
    {
        return $this->query->updateRaw($start);
    }

    public function deleteRaw(string $start)
    {
        return $this->query->deleteRaw($start);
    }

    public function whereRaw(string $start)
    {
        return $this->query->whereRaw($start);
    }

    public function __get(string $name)
    {
        if (!isset($this->data[$name])) {
            throw new PropertyNotFoundException("The property '{$name}' was not found");
        }

        return $this->data[$name];
    }

    public function __set(string $name, mixed $value)
    {
        if (!$this->allowNonExistingProperties && !isset($this->data[$name])) {
            throw new PropertyNotFoundException("The property '{$name}' was not found");
        }

        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function save(): bool
    {
        if (!$this->existingRecord) {
            return $this->query->insert($this->data)->exec();
        }

        $data = $this->data;
        array_shift($data);

        return $this->query->update($data)->whereRaw("{$this->primaryKey} = ?")->addValue($this->{$this->primaryKey})->exec();
    }

    public function jsonSerialize(): object
    {
        return (object) $this->data;
    }

    /**
     * @param bool $existingRecord
     */
    public function setExistingRecord(bool $existingRecord): void
    {
        $this->existingRecord = $existingRecord;
    }

    public static function find(array | int $options = []): Query
    {
        $instance = new static();
        $query = new Query(db(), $instance->table);

        if (is_array($options) && empty($options)) {
            return $query->select([]);
        }

        if (is_array($options)) {
            $query->select($options['columns'] ?? []);

            if (isset($options['where']) && is_array($options['where'])) {
                foreach ($options['where'] as $col => $val) {
                    $query->where($col, $val);
                }
            }

            if (isset($options['orderBy'])) {
                $query->orderBy($options['orderBy']['column'], $options['orderBy']['desc'] ?? false);
            }

            return $query;
        }

        return $query->select([])->where($instance->primaryKey, $options);
    }
}