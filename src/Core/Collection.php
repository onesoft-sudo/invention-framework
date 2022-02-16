<?php


namespace OSN\Framework\Core;


use ArrayAccess;
use Iterator;
use JsonSerializable;
use OSN\Framework\Exceptions\CollectionException;

class Collection implements JsonSerializable, ArrayAccess, Iterator
{
    use CollectionArrayMethods;

    protected array $array;
    protected int $pos = 0;

    /**
     * Collection constructor.
     * @param array $array
     */
    public function __construct(array $array)
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $newArray[$key] = $value;
        }

        $this->array = $newArray;
    }

    /**
     * @throws CollectionException
     */
    public function get($index = null)
    {
        if ($index === null) {
            return $this->array;
        }

        if (!isset($this->array[$index])) {
            throw new CollectionException("Collection key '{$index}' doesn't exist");
        }

        return $this->array[$index];
    }

    public function _($index)
    {
        return $this->get($index);
    }

    public function __get($key)
    {
        $index = $key;

        if ($key[0] === '_')
            $index = substr($key, 1);

        return $this->_($index);
    }

    public function __set($key, $value)
    {
        $index = $key;

        if (is_string($key) && $key[0] === '_')
            $index = substr($key, 1);

        $this->array[$key] = $value;
    }

    public function set($key, $value)
    {
        $this->__set($key, $value);
    }

    public function jsonSerialize(): array
    {
       return $this->array;
    }

    public function __invoke(): array
    {
        return $this->array;
    }

    public function has($key): bool
    {
        try {
            $tmp = $this->get($key);
            return true;
        }
        catch (CollectionException $e) {
            return false;
        }
    }

    public function hasGet($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return null;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->array[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    public function diff(Collection $newUsers)
    {
        return array_diff($this->array, $newUsers->array);
    }

    public function udiff(Collection $newUsers, \Closure $callback)
    {
        return array_udiff($this->array, $newUsers->array, $callback);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->array[$this->pos];
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->pos++;
    }

    /**
     * @return bool|float|int|string|null
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->pos;
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid(): bool
    {
        return isset($this->array[$this->pos]);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->pos = 0;
    }
}
