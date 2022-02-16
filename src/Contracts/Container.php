<?php


namespace OSN\Framework\Contracts;


use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function bind($abstract, \Closure $callback, ?string $prop = null, bool $once = false);
    public function bindOnce($abstract, \Closure $callback, string $prop = null);
    public function resolve($abstract);
}
