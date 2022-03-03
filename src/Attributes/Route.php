<?php


namespace OSN\Framework\Attributes;

use Attribute;

/**
 * Class Route
 *
 * @package OSN\Framework\Attributes
 * @author Ar Rakin <rakinar2@gmail.com>
 */
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public string $route;
    public string $method;
    public string $name;

    /**
     * Route constructor.
     * @param string $route
     * @param string $method
     * @param string $name
     */
    public function __construct(string $route, string $method = 'GET', string $name = '')
    {
        $this->route = $route;
        $this->name = $name;
        $this->method = strtoupper($method);
    }
}