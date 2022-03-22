<?php
/*
 * Copyright 2020-2022 OSN Software Foundation, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OSN\Framework\Events;


use OSN\Framework\Exceptions\EventException;

/**
 * This trait adds a public on() method which allows the user to
 * add event listeners on that object.
 *
 * @package OSN\Framework\Events
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait TriggersEvent
{
    /**
     * Convert short event names to full class name.
     *
     * @param string $event
     * @return string
     * @throws EventException
     */
    public static function eventToClass(string $event): string
    {
        if (!class_exists($event))
            $event = "App\\Events\\{$event}Event";

        if (!class_exists($event)) {
            throw new EventException("Event '$event' not found");
        }

        return $event;
    }

    /**
     * Register an event handler.
     *
     * @param string $event
     * @param $callable
     * @throws EventException
     */
    public static function on(string $event, $callable)
    {
        static::eventToClass($event)::addHandler($callable);
    }

    /**
     * Dispatch an event.
     *
     * @param string $event
     * @param array $args
     * @return mixed
     * @throws EventException
     */
    public static function dispatch(string $event, array $args = [])
    {
        return static::eventToClass($event)::fire($args);
    }
}
