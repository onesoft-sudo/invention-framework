<?php


namespace OSN\Framework\Events;

/**
 * Interface EventHandler
 *
 * @package OSN\Framework\Events
 * @author Ar Rakin <rakinar2@gmail.com>
 */
interface EventHandler
{
    /**
     * Handle the event.
     *
     * @param EventInterface $event
     * @return mixed
     */
    public function handle(EventInterface $event);
}
