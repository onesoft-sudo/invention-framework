<?php

namespace OSN\Framework\Tests\Feature\Routing;

use OSN\Framework\Core\App;
use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;
use OSN\Framework\Routing\Route;
use OSN\Framework\Routing\Router;
use OSN\Framework\Tests\Feature\TestCase;

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

        $this->assertSame($this->route->name(), $route->name());
        $this->assertSame($this->route->path(), $route->path());
        $this->assertSame($this->route->method(), $route->method());
        $this->assertSame($this->route->middleware(), $route->middleware());
        $this->assertSame($this->route->action(), $route->action());
        $this->assertSame($this->route->params(), $route->params());
    }

    /** @test */
    public function route_helper_function_should_fail_with_wrong_route_name()
    {
        App::$app->router->pushRoute($this->route);
        $route = route('tests.bla-bla-bla');
        $this->assertSame(null, $route);
    }

    /** @test */
    public function route_helper_function_should_work_with_route_one_argument()
    {
        $this->route->path("/user/(\d+)");
        App::$app->router->pushRoute($this->route);
        $route = route('tests.main', 512);
        $this->assertSame('/user/512', $route->path());
    }

    /** @test */
    public function route_helper_function_should_work_with_route_multiple_arguments()
    {
        $this->route->path("/user/(\d+)/post/(\d+)-([A-Za-z0-9]+)");
        App::$app->router->pushRoute($this->route);
        $route = route('tests.main', 512, 2, "firstPost937");
        $this->assertSame('/user/512/post/2-firstPost937', $route->path());
    }
}
