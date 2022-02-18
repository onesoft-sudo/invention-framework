<?php


namespace OSN\Framework\Tests\Feature\Routing;


use OSN\Framework\Core\Controller;
use OSN\Framework\Exceptions\HTTPException;
use OSN\Framework\Http\Request;
use OSN\Framework\Tests\Feature\TestCase;

class RouterTest extends TestCase
{
    protected string $route = '/test';
    protected string $method = 'GET';

    protected function setUp(): void
    {
        $_SERVER['REQUEST_URI'] = &$this->route;
        $_SERVER['REQUEST_METHOD'] = &$this->method;

        parent::setUp();
    }

    protected function updateRequest()
    {
        $this->app->request->uri = $this->route;
        $this->app->request->baseURI = $this->route;
        $this->app->request->method = $this->method;
    }

    /** @test */
    public function successfully_registered_a_route()
    {
        $str = 'Hello world!';

        $route = $this->app->router->get("/test", function () use ($str) {
            return $str;
        });

        $routes = $this->app->router->routes();

        $this->assertCount(1, $routes);
        $this->assertSame($route, $routes[0]);
    }

    /** @test */
    public function successfully_registered_a_get_route()
    {
        $str = 'Hello world!';

        $route = $this->app->router->get("/test", function () use ($str) {
            return $str;
        });

        $routes = $this->app->router->routes();

        $this->assertSame($route->method(), 'GET');
        $this->assertSame($routes[0]->method(), 'GET');
    }

    /** @test */
    public function successfully_registered_a_post_route()
    {
        $str = 'Hello world!';

        $route = $this->app->router->post("/test", function () use ($str) {
            return $str;
        });

        $routes = $this->app->router->routes();

        $this->assertSame($route->method(), 'POST');
        $this->assertSame($routes[0]->method(), 'POST');
    }

    /** @test */
    public function successfully_registered_a_put_route()
    {
        $str = 'Hello world!';

        $route = $this->app->router->put("/test", function () use ($str) {
            return $str;
        });

        $routes = $this->app->router->routes();

        $this->assertSame($route->method(), 'PUT');
        $this->assertSame($routes[0]->method(), 'PUT');
    }

    /** @test */
    public function successfully_registered_a_patch_route()
    {
        $str = 'Hello world!';

        $route = $this->app->router->patch("/test", function () use ($str) {
            return $str;
        });

        $routes = $this->app->router->routes();

        $this->assertSame($route->method(), 'PATCH');
        $this->assertSame($routes[0]->method(), 'PATCH');
    }

    /** @test */
    public function successfully_registered_a_delete_route()
    {
        $str = 'Hello world!';

        $route = $this->app->router->delete("/test", function () use ($str) {
            return $str;
        });

        $routes = $this->app->router->routes();

        $this->assertSame($route->method(), 'DELETE');
        $this->assertSame($routes[0]->method(), 'DELETE');
    }

    /** @test */
    public function successfully_resolving_registered_routes()
    {
        $str = 'Hello world!';

        $this->app->router->get("/test", function () use ($str) {
            return $str;
        });

        $output = $this->app->router->resolve();
        $this->assertSame($str, $output);
    }

    /** @test */
    public function can_not_resolve_non_registered_routes()
    {
        $str = 'Hello world!';

        $this->app->router->get("/test2", function () use ($str) {
            return $str;
        });

        $this->expectException(HTTPException::class);
        $this->expectExceptionCode(404);

        $this->app->router->resolve();
    }

    /** @test */
    public function can_not_resolve_registered_routes_with_unsupported_method()
    {
        $str = 'Hello world!';

        $this->app->router->post("/test", function () use ($str) {
            return $str;
        });

        $this->expectException(HTTPException::class);
        $this->expectExceptionCode(405);

        $this->app->router->resolve();
    }

    /** @test */
    public function router_is_working_with_one_route_argument()
    {
        $this->route = '/test/512';
        $this->updateRequest();

        $str = 'Hello world!';

        $this->app->router->get("/test/(\d+)", function () use ($str) {
            return $str;
        });

        $output = $this->app->router->resolve();

        $this->assertSame($str, $output);
    }

    /** @test */
    public function router_is_working_with_multiple_route_arguments()
    {
        $this->route = '/test/512/user/123/7-howToMakeThinks63';
        $this->updateRequest();

        $str = 'Hello world!';

        $this->app->router->get("/test/(\d+)/user/(\d+)/(\d+)-([0-9A-Za-z]+)", function () use ($str) {
            return $str;
        });

        $output = $this->app->router->resolve();

        $this->assertSame($str, $output);
    }
}
