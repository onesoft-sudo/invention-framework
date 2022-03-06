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

namespace OSN\Framework\Contracts;

/**
 * Interface Event
 *
 * @package OSN\Framework\Contracts\Event
 * @author Ar Rakin <rakinar2@gmail.com>
 */
interface Event
{
    /**
     * Event constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = []);

    /**
     * Execute the event.
     *
     * @return mixed
     */
    public function execute();

    /**
     * Stops executing the event.
     *
     * @return mixed
     */
    public function stop();

    /**
     * Change the status of the event to fired.
     *
     * @return void
     */
    public function setFired();

    /**
     * Get the handler(s) of the event.
     *
     * @return array
     */
    public static function getHandlers(): array;

    /**
     * Set the handler for the event.
     *
     * @param callable $callback
     * @return void
     */
    public static function setHandler(callable $callback);

    /**
     * Add handler for the event.
     *
     * @param callable $callback
     * @return void
     */
    public static function addHandler(callable $callback);

    /**
     * Call the event handlers.
     *
     * @return mixed
     */
    public function fireHandlers();

    /**
     * Fire an event.
     */
    public static function fire(array $data = []);
}
