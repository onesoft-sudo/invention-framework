<?php


namespace OSN\Framework\ORM;


use OSN\Framework\DataTypes\_String;

abstract class Model extends ActiveRecord
{
    public function generateTableName(): string
    {
        $className = explode('\\', static::class);
        $className = end($className);
        return _String::from($className)->camel2slug()->__toString() . 's';
    }

    public static function query(): Query
    {
        return (new static())->query;
    }
}