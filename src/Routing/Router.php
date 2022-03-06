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

namespace OSN\Framework\Routing;

use Closure;
use OSN\Framework\Core\App;
use OSN\Framework\Core\Controller;
use OSN\Framework\Exceptions\FileNotFoundException;
use OSN\Framework\Exceptions\HTTPException;
use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;
use OSN\Framework\Facades\Response as ResponseFacade;
use OSN\Framework\View\View;
use stdClass;

class Router
{
    use HTTPRouteRegistrationTrait;
    use HTTPRouteControllerAssignerTrait;

    public Request $request;
    public Response $response;

    /**
     * Router constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function pushRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @param string $givenPath
     * @param string $givenMethod
     * @return false|Route
     */
    public function findRoute(string $givenPath, string $givenMethod = ''): Route|bool
    {
        foreach ($this->routes as $route) {
            if ($route->path() === $givenPath || $route->matches($givenPath)) {
                if ($givenMethod === '') {
                    return $route;
                }
                else {
                    if ($route->method() == $givenMethod)
                        return $route;
                }
            }
        }

        return false;
    }

    public function findByLogic(Closure $closure): Route|bool
    {
        foreach ($this->routes as $route) {
            if (call_user_func_array($closure, [$route]) === true) {
                return $route;
            }
        }

        return false;
    }

    public function autoRegister(array $actions): void
    {
        foreach ($actions as $action) {
            if (is_string($action)) {
                $reflectionRoute = new \ReflectionClass($action);
                $methods = $reflectionRoute->getMethods();

                foreach ($methods as $method) {
                    $attrs = $method->getAttributes(\OSN\Framework\Attributes\Route::class,\ReflectionAttribute::IS_INSTANCEOF);

                    foreach ($attrs as $attr) {
                        $attr = $attr->newInstance();
                        $r = $this->addRoute($attr->method, $attr->route, [$action, $method->getName()]);

                        if ($attr->name !== '') {
                            $r->name($attr->name);
                        }
                    }
                }
            }
            elseif ($action instanceof Closure) {
                $function = new \ReflectionFunction($action);
                $attrs = $function->getAttributes(\OSN\Framework\Attributes\Route::class,\ReflectionAttribute::IS_INSTANCEOF);

                foreach ($attrs as $attr) {
                    $attr = $attr->newInstance();
                    $r = $this->addRoute($attr->method, $attr->route, $action);

                    if ($attr->name !== '') {
                        $r->name($attr->name);
                    }
                }
            }
        }
    }

    public function registerAllControllers(): void
    {
        $controllers = scandir(basepath('/app/Http/Controllers'));
        $actions = [];

        foreach ($controllers as $controller) {
            if ($controller === '.' || $controller === '..')
                continue;

            $actions[] = "\\App\\Http\\Controllers\\" . pathinfo($controller, PATHINFO_FILENAME);
        }

        $this->autoRegister($actions);
    }

    /**
     * @throws HTTPException|FileNotFoundException
     */
    public function resolve()
    {
        $path = $this->request->baseURI;
        $method = $this->request->method;

        $route = $this->findRoute($path, $method);
        $anyRoute = $this->findRoute($path);

        if ($method !== 'HEAD' && $route == false && $anyRoute != false) {
            throw new HTTPException(405);
        }

        if ($method === 'HEAD' && $anyRoute != false) {
            return '';
        }

        if ($route === false) {
            throw new HTTPException(404);
        }

        $callback = $route->action();

        if (is_string($callback)) {
            $callback = new View($callback);
        }

        if (is_array($callback)) {
            /** @var string[]|Controller[] */
            $callback[0] = new $callback[0]();
            $callback[1] = $callback[1] ?? 'index';
            $globals = [];

            $globalMiddlewares = config('http')['global_middleware'] ?? [];

            foreach ($globalMiddlewares as $globalMiddleware) {
                if (is_object($globalMiddleware)) {
                    $globals[] = $globalMiddleware;
                    continue;
                }

                $globals[] = new $globalMiddleware();
            }

            $middleware = array_merge($globals, $route->middleware(), $callback[0]->getMiddleware());

            $userMiddlewareMethods = $callback[0]->getMiddlewareMethods();

            foreach ($middleware as $m) {
                if ((!in_array($m, $globals) && ((!empty($userMiddlewareMethods) && in_array($callback[1], $userMiddlewareMethods)) || empty($userMiddlewareMethods))) || in_array($m, $globals)) {
                    $middlewareResponse = $m->execute(App::$app->request);

                    if($middlewareResponse === true || $middlewareResponse === null){
                        continue;
                    }

                    ResponseFacade::setCode($middlewareResponse instanceof Response ? $middlewareResponse->getCode() : 200);

                    return $middlewareResponse;
                }
            }

            $params = app()->prepareFunctionCallParams(...$callback);

            /*
            if (isset($params[0])) {
                $request = new $params[0]();

                if ($request instanceof Request) {
                    if (!$request->authorize()) {
                        throw new HTTPException(403, "Forbidden");
                    }

                    if ($request->autoValidate && !$request->validate()) {
                        if (method_exists($request, 'handleInvalid')) {
                            $request->handleInvalid();
                        }
                        else {
                            if ($this->request->header('Referer')) {
                                $this->response->setCode(406);
                                $this->response->redirect($this->request->header('Referer'));
                                return '';
                            }
                            else {
                                throw new HTTPException(406);
                            }
                        }
                    }
                }
            }*/

            $params = array_merge($params, $route->params());
        }

        //$output = call_user_func_array($callback, [$request ?? App::request()]);
        $output = call_user_func_array($callback, $params ?? ([$request ?? App::request()]));

        if (is_jsonable($output) && !($output instanceof Response || $output instanceof View)) {
            $this->response->header("Content-Type", "application/json");
            return json_encode($output, env('APP_ENV') != "production" ? JSON_PRETTY_PRINT : 0);
        }

        return $output;
    }
}
