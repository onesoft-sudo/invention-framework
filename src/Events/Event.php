<?php


namespace OSN\Framework\Events;

use OSN\Framework\Exceptions\EventException;
use phpDocumentor\Reflection\Types\Callable_;

/**
 * Class Event
 *
 * @package OSN\Framework\Events
 * @author Ar Rakin <rakinar2@gmail.com>
 */
abstract class Event implements EventInterface
{
    protected bool $fired;

    /**
     * @var callable[]
     */
    protected static array $handlers = [];

    public string $timestamp;
    public int $statusCode;

    public function __construct(array $data = [])
    {
        $this->fired = false;

        $this->timestamp = date(DATE_ATOM);
        $this->statusCode = $data["statusCode"] ?? 0;
    }

    /**
     * @return false|string
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    public function timestamp()
    {
        return $this->timestamp;
    }

    public function fired()
    {
        $this->fired = true;
    }

    public function isFired(): bool
    {
        return $this->fired;
    }

    public static function getHandlers(): array
    {
        return static::$handlers;
    }

    public static function setHandler($callback)
    {
        static::$handlers = [$callback];
    }

    public static function addHandler($callback)
    {
        static::$handlers[] = $callback;
    }

    /**
     * @return mixed
     */
    public function fireHandlers()
    {
        $data = null;

        foreach (static::$handlers as $handler) {
            if (is_array($handler)) {
                if (is_string($handler[0]))
                    $handler[0] = new $handler[0]();

                if (!isset($handler[1]))
                    $handler[1] = 'handle';
            }

            if (is_string($handler))
                $handler = [new $handler(), 'handle'];

            $data = call_user_func($handler, $this);
        }

        return $data;
    }

    public static function fire(array $data = [])
    {
        $event = new static($data);
        $event->fired();
        return $event->fireHandlers();
    }

    public function execute()
    {}

    public function prevent()
    {}

    public function stop()
    {}
}
