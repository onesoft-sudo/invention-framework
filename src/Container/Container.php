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

namespace OSN\Framework\Container;



use ArrayAccess;
use OSN\Framework\Exceptions\ContainerAbstractNotFoundException;
use OSN\Framework\Foundation\App;
use \OSN\Framework\Contracts\Container as ContainerInterface;

/**
 * The DI container.
 *
 * @package OSN\Framework\Container
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Container implements ContainerInterface, ArrayAccess
{
    /**
     * The bindings.
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * Loads bindings from configuration.
     *
     * @return void
     */
    protected function loadBindingsFromConfig()
    {
        $bindings = config('bindings') ?? [];

        foreach ($bindings as $abstract => $binding) {
            $this->bind($abstract, fn() => ($binding['object'] ?? null), $binding['prop'] ?? null, $binding['once'] ?? false);
        }
    }

    /**
     * Bind an abstract.
     *
     * @param $abstract
     * @param \Closure $callback
     * @param string|null $prop
     * @param bool $once
     * @return $this
     */
    public function bind($abstract, \Closure $callback, ?string $prop = null, bool $once = false): self
    {
        $prop = $prop ?? $abstract;

        $this->bindings[$abstract] = [
            "callback" => $callback,
            "once" => $once,
            "prop" => $prop,
            "object" => call_user_func($callback)
        ];

        return $this;
    }

    public function __get(string $name)
    {
        foreach ($this->bindings as $abstract => $binding) {
            if ($name === $abstract || $binding['prop'] === $name) {
                return $this->resolve($abstract);
            }
        }

        return null;
    }

    /**
     * Get function/method parameter types from its Reflection.
     *
     * @param $reflection
     * @param bool $requiredOnly
     * @param bool $associative
     * @return array
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    public function getParamTypes($reflection, bool $requiredOnly = false, bool $associative = false): array
    {
        $attr = $reflection->getParameters();

        $params = [];

        foreach ($attr as $att) {
            if ($requiredOnly && $att->isOptional())
                continue;

            $type = $att->getType();
            $typeName = $type === null ? 'mixed' : $type->getName();

            if ($associative) {
                $params[$att->name] = $typeName;
            }
            else {
                $params[] = $typeName;
            }
        }

        return $params;
    }

    /**
     * Get the required parameters of a method.
     *
     * @param $objOrMethod
     * @param null $method
     * @param bool $associative
     * @return array
     * @throws \ReflectionException
     */
    public function getMethodRequiredParamTypes($objOrMethod, $method = null, bool $associative = false): array
    {
        return $this->getParamTypes(new \ReflectionMethod($objOrMethod, $method), true, $associative);
    }

    /**
     * Get the required params of a function.
     *
     * @param $function
     * @param bool $associative
     * @return array
     * @throws \ReflectionException
     */
    public function getFunctionRequiredParamTypes($function, bool $associative = false): array
    {
        return $this->getParamTypes(new \ReflectionFunction($function), true, $associative);
    }

    public function createNewObject(string $class)
    {
        if (!method_exists($class, '__construct')) {
            return new $class();
        }

        $paramsToPass = [];
        $params = app()->getMethodRequiredParamTypes($class, '__construct');

        foreach ($params as $param) {
            $constructorParams = [];

            if (!class_exists($param) && !interface_exists($param)) {
                //$paramsToPass[] = null;
                // throw new \RuntimeException('Unresolvable dependency: ' . $param);
                continue;
            }

            if (method_exists($param, '__construct'))
                $constructorParams = app()->getMethodRequiredParamTypes($param, '__construct');

            if (count($constructorParams) < 1 && !app()->has($param)) {
                $paramToPass = new $param();
            }
            else {
                $paramToPass = app()->resolve($param);
            }

            $paramsToPass[] = $paramToPass;
        }

        return new $class(...$paramsToPass);
    }

    /**
     * Bind an abstract once (AKA Singleton)
     *
     * @param $abstract
     * @param \Closure $callback
     * @param string|null $prop
     * @return $this
     */
    public function bindOnce($abstract, \Closure $callback, string $prop = null): self
    {
        return $this->bind($abstract, $callback, $prop, true);
    }

    /**
     * Returns the params that must be passed to a method/function.
     *
     * @param $objOrMethod
     * @param null $method
     * @return array
     * @throws \ReflectionException
     */
    public function prepareFunctionCallParams($objOrMethod, $method = null): array
    {
        $paramsToPass = [];
        $params = app()->getMethodRequiredParamTypes($objOrMethod, $method);

        foreach ($params as $param) {
            $constructorParams = [];

            if (!class_exists($param) && !interface_exists($param)) {
                //$paramsToPass[] = null;
               // throw new \RuntimeException('Unresolvable dependency: ' . $param);
                continue;
            }

            if (method_exists($param, '__construct'))
                $constructorParams = app()->getMethodRequiredParamTypes($param, '__construct');

            if (count($constructorParams) < 1 && !app()->has($param)) {
                $paramToPass = new $param();
            }
            else {
                $paramToPass = app()->resolve($param);
            }

            $paramsToPass[] = $paramToPass;
        }

        return $paramsToPass;
    }

    /**
     * Checks if the container has a binding with the given ID.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        try {
            $this->resolve($id);
            return true;
        }
        catch (ContainerAbstractNotFoundException $e) {
            if ($e->getCode() === 23) {
                return false;
            }
            else {
                throw new ContainerAbstractNotFoundException($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * Retrieve back an abstract.
     *
     * @param $abstract
     * @return false|mixed
     */
    public function resolve($abstract)
    {
        $a = $this->bindings[$abstract] ?? null;

        if ($a === null) {
            if (class_exists($abstract)) {
                return new $abstract();
            }

            throw new ContainerAbstractNotFoundException("Unresolvable dependency: $abstract");
        }

        if ($a['once'] === true) {
            $obj = $this->bindings[$abstract]['object'] ?? null;

            if ($obj === null) {
                $obj = call_user_func($this->bindings[$abstract]['callback']);
                $this->bindings[$abstract]['object'] = $obj;
            }
        }
        else {
            $obj = call_user_func($this->bindings[$abstract]['callback']);
        }

        return $obj;
    }

    /**
     * Retrieve back an abstract. Same as $this->resolve()
     *
     * @param string $id
     * @return false|mixed
     * @todo
     */
    public function get($id)
    {
        return $this->resolve($id);
    }

    /**
     * Remove an abstract from the container.
     *
     * @param $abstract
     */
    public function remove($abstract)
    {
        if (!$this->has($abstract))
            throw new ContainerAbstractNotFoundException("Unresolvable dependency: $abstract", 23);

        if(isset($this->bindings[$abstract]))
            unset($this->bindings[$abstract]);

        if(isset($this->$abstract))
            unset($this->$abstract);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->$offset);
    }

    /**
     * Get the value of the given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->resolve($offset);
    }

    /**
     * Set the given value of the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->bindOnce($offset, fn() => $value);
    }

    /**
     * Unset the given offset.
     *
     * @param mixed $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
