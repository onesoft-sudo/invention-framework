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
     * @param \OSN\Framework\Contracts\Event $event
     * @return mixed
     */
    public function handle(\OSN\Framework\Contracts\Event $event);
}
