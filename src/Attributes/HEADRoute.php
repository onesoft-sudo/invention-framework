<?php


namespace OSN\Framework\Attributes;

use Attribute;
use Pure;

/**
 * Class HEADRoute
 *
 * @package OSN\Framework\Attributes
 * @author Ar Rakin <rakinar2@gmail.com>
 */
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class HEADRoute extends Route
{
    #[Pure]
    public function __construct(string $route, string $name = '')
    {
        parent::__construct($route, 'HEAD', $name);
    }
}