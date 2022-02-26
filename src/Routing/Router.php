<?php


namespace OSN\Framework\Routing;

use App\Http\Config;
use Closure;
use OSN\Framework\Core\App;
use OSN\Framework\Core\Controller;
use OSN\Framework\Exceptions\FileNotFoundException;
use OSN\Framework\Exceptions\HTTPException;
use OSN\Framework\Http\HTTPMethodControllerHelper;
use OSN\Framework\Http\HTTPMethodRouterHelper;
use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;
use OSN\Framework\Facades\Response as ResponseFacade;
use OSN\Framework\View\View;
use stdClass;

class Router
{
    use HTTPMethodRouterHelper;
    use HTTPMethodControllerHelper;

    /**
     * @var Route[] $routes
     */
    protected array $routes = [];

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
