<?php


namespace OSN\Framework\Core;


use ArrayAccess;

class Config implements ArrayAccess
{
    protected array $conf;
    protected string $conf_file;

    public function __construct($file)
    {
        $this->conf_file = $file;
        $this->load();
    }

    public function __get($name)
    {
        return $this->conf[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->conf[$name] = $value;
    }

    protected function load()
    {
       $this->conf = require($this->conf_file);
    }

    public function getAll(): array
    {
        return $this->conf;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->conf[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->conf[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->conf[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->conf[$offset]);
    }
}
