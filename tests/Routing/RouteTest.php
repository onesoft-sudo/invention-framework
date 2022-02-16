<?php

namespace OSN\Framework\Tests\Routing;

use OSN\Framework\Core\App;
use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;
use OSN\Framework\Routing\Route;
use OSN\Framework\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    private Route $route;
    private string $method;
    private string $path;
    private $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->method = 'get';
        $this->path = '/user';
        $this->action = fn() => "Hello world!";
        $this->route = (new Route($this->method, $this->path, $this->action));
        $this->route->name('tests.main');
        $this->appInit();
    }

    public function appInit()
    {
        App::$app = new class() extends App {
            public \OSN\Framework\Routing\Router $router;
            public \OSN\Framework\Http\Request $request;
            public \OSN\Framework\Http\Response $response;

            public function __construct()
            {
                $this->request = new Request();
                $this->response = new Response();
                $this->router = new Router($this->request, $this->response);
            }
        };
    }

    /** @test */
    public function route_regex_is_matching()
    {
        $this->route = new Route($this->method, '/user/(\d+)', $this->action);
        $this->assertTrue($this->route->matches('/user/55'));
    }

    /** @test */
    public function wrong_route_regex_is_not_matching()
    {
        $this->route = new Route($this->method, '/user/(\d+)', $this->action);
        $this->assertFalse($this->route->matches('/user/55-bla-bla-bla'));
    }

    /** @test */
    public function route_helper_function_is_working_with_route_names()
    {
        App::$app->router->pushRoute($this->route);
        $route = route('tests.main');
        $this->assertSame($this->route, $route);
    }

    /** @test */
    public function route_helper_function_should_fail_with_wrong_route_name()
    {
        App::$app->router->pushRoute($this->route);
        $route = route('tests.bla-bla-bla');
        $this->assertNotSame($this->route, $route);
    }
}
