<?php

namespace OSN\Framework\Tests\Feature\Controllers;

use OSN\Framework\Tests\Feature\TestCase;

class ControllerTest extends TestCase
{
    protected string $route = '/test';
    protected string $method = 'GET';

    protected function setUp(): void
    {
        $_SERVER['REQUEST_URI'] = &$this->route;
        $_SERVER['REQUEST_METHOD'] = &$this->method;

        parent::setUp();
    }

    /** @test */
    public function basic_controllers_are_working()
    {
        $this->app->router->get('/test', [MainController::class, 'index']);
        $this->assertSame($this->app->router->resolve(), 'Hello world!');
    }

    /** @test */
    public function basic_controllers_are_working_with_post_route()
    {
        $_POST['data1'] = '123';
        $_POST['data2'] = 'string';
        $this->method = 'POST';

        $this->updateRequest();

        $data = [
            '123' => 123,
            'data' => [
                'data1' => '123',
                'data2' => 'string'
            ]
        ];

        $this->app->router->post('/test', [MainController::class, 'store']);
        $this->assertSame($this->app->router->resolve(), json_encode($data, JSON_PRETTY_PRINT));
    }

    /** @test */
    public function basic_api_controllers_are_working_with_assignAPIController_method()
    {
        $this->app->router->assignAPIController('/api', APIController::class);

        $this->route = '/api';
        $this->updateRequest();
        $index = $this->app->router->resolve();

        $this->route = '/api/65';
        $this->updateRequest();
        $view = $this->app->router->resolve();

        $this->route = '/api';
        $this->method = 'POST';
        $this->updateRequest();
        $store = $this->app->router->resolve();

        $this->route = '/api/34';
        $this->method = 'PUT';
        $this->updateRequest();
        $update_put = $this->app->router->resolve();

        $this->route = '/api/34';
        $this->method = 'PATCH';
        $this->updateRequest();
        $update_patch = $this->app->router->resolve();

        $this->route = '/api/34';
        $this->method = 'DELETE';
        $this->updateRequest();
        $delete = $this->app->router->resolve();

        $this->assertSame($index,"index");
        $this->assertSame($view,"view");
        $this->assertSame($store,"store");
        $this->assertSame($update_put,"update");
        $this->assertSame($update_patch,"update");
        $this->assertSame($delete,"delete");
    }
}