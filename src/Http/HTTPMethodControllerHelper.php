<?php


namespace OSN\Framework\Http;

trait HTTPMethodControllerHelper
{
    protected array $apiHandlers = [
        "get" => ["index", "view"],
        "post" => "store",
        "put" => "update",
        "patch" => "update",
        "delete" => "delete",
    ];

    protected array $webHandlers = [
        "get" => "index",
        "post" => "create",
    ];

    public function assignAPIController(string $route, string $controller, ?array $handlers = null)
    {
        if (class_exists($controller)) {
            if ($handlers !== null) {
                $this->apiHandlers = $handlers;
            }

            $array = explode("\\", $controller);
            $name = strtolower(preg_replace('/Controller$/', '', end($array)));

            $this->get($route, [$controller, $this->apiHandlers['get'][0]])->name($name . "." . $this->apiHandlers['get'][0]);
            $this->get($route . "/view", [$controller, $this->apiHandlers['get'][1]])->name($name . "." . $this->apiHandlers['get'][1]);
            $this->post($route, [$controller, $this->apiHandlers['post']])->name($name . "." . $this->apiHandlers['post'][0]);
            $this->put($route, [$controller, $this->apiHandlers['put']])->name($name . "." . $this->apiHandlers['put'][0]);
            $this->patch($route, [$controller, $this->apiHandlers['patch']])->name($name . "." . $this->apiHandlers['patch'][0]);
            $this->delete($route, [$controller, $this->apiHandlers['delete']])->name($name . "." . $this->apiHandlers['delete'][0]);
        }
    }

    /**
     * @param string $route
     * @param string $controller
     * @param array|null $handlers
     * @todo
     */
    public function assignWebController(string $route, string $controller, ?array $handlers = null)
    {
        if (class_exists($controller)) {
            if ($handlers !== null) {
                $this->webHandlers = $handlers;
            }

            $this->get($route, [$controller, $this->webHandlers['get']]);
            $this->post($route, [$controller, $this->webHandlers['post']]);
        }
    }
}
