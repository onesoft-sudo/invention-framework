<?php


namespace OSN\Framework\Contracts;


use Closure;
use Psr\Container\ContainerInterface;

/**
 * Interface Container
 *
 * @package OSN\Framework\Contracts
 * @author Ar Rakin <rakinar2@gmail.com>
 */
interface Container extends ContainerInterface
{
    /**
     * Binds dependencies into the container.
     *
     * @param $abstract
     * @param Closure $callback
     * @param string|null $prop
     * @param bool $once
     * @return mixed
     */
    public function bind($abstract, Closure $callback, ?string $prop = null, bool $once = false);

    /**
     * Binds dependencies only once (creates an object once).
     *
     * @param $abstract
     * @param Closure $callback
     * @param string|null $prop
     * @return mixed
     */
    public function bindOnce($abstract, Closure $callback, string $prop = null);

    /**
     * Resolve bindings within their property name or class name.
     *
     * @param $abstract
     * @return mixed
     */
    public function resolve($abstract);
}
