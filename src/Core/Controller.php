<?php


namespace OSN\Framework\Core;


use OSN\Framework\View\Layout;
use OSN\Framework\View\View;

abstract class Controller
{
    /**
     * @var Middleware[] $middlewares
     */
    protected array $middlewares = [];
    protected array $middlewareMethods = [];

    public function render($view, array $data = [], $layout = ''): View
    {
        if ($view instanceof View) {
            return $view;
        }

        return new View($view, $data, $layout);
    }

    protected function setLayout($layout)
    {
        if ($layout instanceof Layout) {
            $name = $layout->getName();
        }
        else {
            $name = $layout;
        }

        App::$app->config["layout"] = str_replace('.', '/', $name);
    }

    /**
     * @param Middleware[]|string[] $middlewares
     */
    protected function setMiddleware(array $middlewares, array $methods = [])
    {
        if (is_string($middlewares[0])) {
            foreach ($middlewares as $key => $middleware) {
                $middlewares[$key] = is_string($middleware) ? new $middleware() : $middleware;
            }
        }

        $this->middlewares = array_merge($this->middlewares, $middlewares);
        $this->middlewareMethods = $methods;
    }

    public function getMiddlewareMethods(): array
    {
        return $this->middlewareMethods;
    }

    public function getMiddleware(): array
    {
        return $this->middlewares;
    }

    protected function db(): Database
    {
        return App::$app->db;
    }

    protected function session(): Session
    {
        return App::$app->session();
    }
}
