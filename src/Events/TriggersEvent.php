<?php


namespace OSN\Framework\Events;


use OSN\Framework\Exceptions\EventException;

trait TriggersEvent
{
    public static function eventToClass(string $event): string
    {
        if (!class_exists($event))
            $event = "App\\Events\\{$event}Event";

        if (!class_exists($event)) {
            throw new EventException("Event '$event' not found");
        }

        return $event;
    }

    public static function on(string $event, $callable)
    {
        static::eventToClass($event)::addHandler($callable);
    }

    public static function dispatch(string $event, array $args = [])
    {
        return static::eventToClass($event)::fire($args);
    }
}
