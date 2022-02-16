<?php


namespace OSN\Framework\Utils;

/**
 * Interface Arrayable
 *
 * @package OSN\Framework\Utils
 * @author Ar Rakin <rakinar2@gmail.com>
 */
interface Arrayable
{
    /**
     * Return the array representation of the current object.
     *
     * @return array
     */
    public function toArray(): array;
}
