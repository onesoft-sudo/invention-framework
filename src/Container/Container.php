<?php


namespace OSN\Framework\Container;



use OSN\Framework\Exceptions\ContainerAbstractNotFoundException;
use OSN\Framework\Foundation\App;
use \OSN\Framework\Contracts\Container as ContainerInterface;

class Container implements ContainerInterface
{
    protected array $bindings = [];

    protected function loadBindingsFromConfig()
    {
        $bindings = config('bindings') ?? [];

        foreach ($bindings as $abstract => $binding) {
            $this->bind($abstract, fn() => ($binding['object'] ?? null), $binding['prop'] ?? null, $binding['once'] ?? false);
        }
    }

    public function bind($abstract, \Closure $callback, ?string $prop = null, bool $once = false): self
    {
        $prop = $prop ?? $abstract;

        $this->bindings[$abstract] = [
            "callback" => $callback,
            "once" => $once,
            "prop" => $prop
        ];

        $this->$prop = call_user_func($callback);

        return $this;
    }

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
     * @throws \ReflectionException
     */
    public function getMethodRequiredParamTypes($objOrMethod, $method = null, bool $associative = false): array
    {
        return $this->getParamTypes(new \ReflectionMethod($objOrMethod, $method), true, $associative);
    }

    /**
     * @throws \ReflectionException
     */
    public function getFunctionRequiredParamTypes($function, bool $associative = false): array
    {
        return $this->getParamTypes(new \ReflectionFunction($function), true, $associative);
    }

    public function bindOnce($abstract, \Closure $callback, string $prop = null): self
    {
        return $this->bind($abstract, $callback, $prop, true);
    }

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

    public function resolve($abstract)
    {
        $a = $this->bindings[$abstract] ?? null;

        if ($a === null) {
            $a = $this->$abstract ?? null;

            if ($a !== null)
                return $a;
        }

        if ($a === null) {
            throw new ContainerAbstractNotFoundException("Unresolvable dependency: $abstract", 23);
        }

        if (!$a['once']) {
            return call_user_func($a['callback']);
        }

        return $this->{$a['prop']};
    }

    public function get($id)
    {
        return $this->resolve($id);
    }
}
