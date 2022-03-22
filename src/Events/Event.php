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

use Carbon\Carbon;
use OSN\Framework\Exceptions\EventException;
use OSN\Framework\Contracts\Event as EventInterface;

/**
 * Base event class.
 *
 * @package OSN\Framework\Events
 * @author Ar Rakin <rakinar2@gmail.com>
 * @todo
 */
abstract class Event implements EventInterface
{
    /**
     * Determine if the event is fired.
     *
     * @var bool
     */
    protected bool $fired;

    /**
     * Handlers of this event.
     *
     * @var callable[]
     */
    protected static array $handlers = [];

    /**
     * The timestamp.
     *
     * @var Carbon
     */
    public Carbon $timestamp;

    /**
     * Event status code.
     *
     * @var int|mixed
     */
    public int $statusCode;

    /**
     * Event constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->fired = false;

        $this->timestamp = Carbon::now();
        $this->statusCode = $data["statusCode"] ?? 0;
    }

    /**
     * Get the timestamp.
     *
     * @return Carbon
     */
    public function timestamp(): Carbon
    {
        return $this->timestamp;
    }

    /**
     * Set the event status to fired.
     *
     * @return void
     */
    public function setFired()
    {
        $this->fired = true;
    }

    /**
     * Determine if the event is fired.
     *
     * @return bool
     */
    public function isFired(): bool
    {
        return $this->fired;
    }

    /**
     * Get handlers of this invent.
     *
     * @return callable[]
     */
    public static function getHandlers(): array
    {
        return static::$handlers;
    }

    /**
     * Set handler for this event.
     *
     * @param callable $callback
     */
    public static function setHandler($callback)
    {
        static::$handlers = [$callback];
    }

    /**
     * Add a new handler of this event.
     *
     * @param callable $callback
     */
    public static function addHandler($callback)
    {
        static::$handlers[] = $callback;
    }

    /**
     * Fire all handlers of this event.
     *
     * @return mixed
     */
    public function fireHandlers()
    {
        $data = null;

        foreach (static::$handlers as $handler) {
            if (is_array($handler)) {
                if (is_string($handler[0]))
                    $handler[0] = app()->createNewObject($handler[0]);

                if (!isset($handler[1]))
                    $handler[1] = 'handle';
            }

            if (is_string($handler))
                $handler = [new $handler(), 'handle'];

            $data = call_user_func($handler, $this);
        }

        return $data;
    }

    /**
     * Fire the event.
     *
     * @param array $data
     * @return mixed|void|null
     */
    public static function fire(array $data = [])
    {
        $event = new static($data);
        $event->setFired();
        return $event->fireHandlers();
    }

    /**
     * Execute the event.
     *
     * @return mixed|void
     */
    public function execute()
    {}

    /**
     * Stop executing the event.
     *
     * @return mixed|void
     */
    public function stop()
    {}
}
