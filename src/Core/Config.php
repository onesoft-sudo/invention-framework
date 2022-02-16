<?php


namespace OSN\Framework\Core;


use ArrayAccess;

class Config implements ArrayAccess
{
    protected array $conf;
    protected string $conf_dir;

    public function __construct($rootpath)
    {
        $this->conf_dir = $rootpath;
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
        $conffiles = scandir($this->conf_dir);
        $conf = [];

        foreach ($conffiles as $conffile) {
            $conffile = $this->conf_dir . '/' . $conffile;
            if (is_file($conffile) && pathinfo($conffile, PATHINFO_EXTENSION) == 'php') {
                $arrConf = include($conffile);
                $conf = array_merge($conf, $arrConf);
            }
        }

        $this->conf = $conf;
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
