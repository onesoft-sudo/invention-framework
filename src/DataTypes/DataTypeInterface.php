<?php


namespace OSN\Framework\DataTypes;


/**
 * Interface DataTypeInterface
 * @package OSN\Framework\DataTypes
 */
interface DataTypeInterface
{
    /**
     * @return mixed
     */
    public function __toString();

    /**
     * @return mixed
     */
    public function get();

    /**
     * @param $value
     * @return static|void
     */
    public function set($value);
}
