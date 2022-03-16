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

namespace OSN\Framework\Core;


use OSN\Framework\Exceptions\HTTPException;
use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;
use OSN\Framework\Routing\Router;
use OSN\Framework\View\View;

/**
 * Class App
 *
 * @package App\Core
 * @property-read Request $request
 * @property-read Response $response
 * @property-read Router $router
 * @property-read Session $session
 */
class App extends \OSN\Framework\Foundation\App
{
    /**
     * @throws \Exception
     */
    public function boot()
    {
        $this->bind(Session::class, fn() => new Session(), 'session', true);
        $this->bind(Response::class, fn() => new Response(), 'response', true);
        $this->bind(Request::class, fn() => new Request(), 'request', true);
        $this->bind(Router::class, fn() => new Router($this->request, $this->response), 'router', true);
    }

    public static function session(): Session
    {
        return self::$app->session;
    }

    public static function db(): Database
    {
        return self::$app->db;
    }

    public static function request(): Request
    {
        return self::$app->request;
    }

    public static function response(): Response
    {
        return self::$app->response;
    }

    public static function config($key)
    {
        return self::$app->config[$key];
    }

    public function run()
    {
        try {
            $this->afterinit();
            $output = $this->router->resolve();
            ($this->response)();
            echo $output;
        }
        catch (HTTPException $e) {
            $this->response->setCode($e->getCode());
            $this->response->setStatusText($e->getMessage());
            $this->response->setHeadersParsed($e->getHeaders());
            ($this->response)();

            if (view_exists("errors." . $e->getCode()))
                echo new View("errors." . $e->getCode(), ["uri" => $this->request->baseURI, "method" => $this->request->method], 'layouts.error');
        }
        catch (\Throwable $e) {
            echo new View('errors.exception', [
                "exception" => $e
            ], null);
        }
        finally {
            parent::run();
        }
    }
}
