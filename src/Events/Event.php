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

    public Carbon $timestamp;
    public int $statusCode;

    public function __construct(array $data = [])
    {
        $this->fired = false;

        $this->timestamp = Carbon::now();
        $this->statusCode = $data["statusCode"] ?? 0;
    }

    /**
     * @return Carbon
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    public function timestamp(): Carbon
    {
        return $this->timestamp;
    }

    public function setFired()
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
        $event->setFired();
        return $event->fireHandlers();
    }

    public function execute()
    {}

    public function stop()
    {}
}
