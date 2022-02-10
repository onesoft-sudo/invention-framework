<?php


namespace OSN\Framework\Events;

/**
 * Interface EventInterface
 *
 * @package OSN\Framework\Events
 * @author Ar Rakin <rakinar2@gmail.com>
 */
interface EventInterface
{
    /**
     * EventInterface constructor.
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
     * Prevent the event's default behaviour.
     *
     * @return mixed
     */
    public function prevent();

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
    public function fired();

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
     * Fire the event handlers.
     *
     * @return mixed
     */
    public function fireHandlers();

    /**
     * Fire an event.
     */
    public static function fire(array $data = []);
}
