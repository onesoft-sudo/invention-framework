<?php

namespace OSN\Framework\Facades;

use OSN\Framework\Core\Facade;

/**
 * @method static create(string $string, \Closure $param)
 * @method static dropIfExists(string $string)
 * @method static exec(string $string)
 */
class Schema extends Facade
{
    protected static string $className = \OSN\Framework\Database\Schema::class;
}
