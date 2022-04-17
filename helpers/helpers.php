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

use Carbon\Carbon;
use OSN\Framework\Contracts\Event;
use OSN\Framework\Core\Model;
use OSN\Framework\Exceptions\EventException;
use OSN\Framework\Routing\Route;
use OSN\Framework\Security\Auth;
use OSN\Framework\Utils\Arrayable;
use OSN\Framework\Core\App;
use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Session;
use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;
use OSN\Framework\Utils\Security\CSRF;
use OSN\Framework\View\View;


if (!function_exists("auth")) {
    /**
     * Return a new Auth instance.
     *
     * @return Auth
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function auth(): Auth
    {
        return new Auth();
    }
}

if (!function_exists("ddr")) {
    /**
     * Die-dump raw data.
     *
     * @param mixed $param
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function ddr(mixed $param)
    {
        echo "<pre>";
        var_dump($param);
        echo "</pre>";
        exit();
    }
}

if (!function_exists("dp")) {
    /**
     * Die-dump data using print_r().
     *
     * @param $param
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function dp($param)
    {
        echo "<pre>";
        print_r($param);
        echo "</pre>";
        exit();
    }
}

if (!function_exists("de")) {
    /**
     * Die-dump data using echo().
     *
     * @param $param
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function de($param)
    {
        echo "<pre>";
        echo($param);
        echo "</pre>";
        exit();
    }
}

if (!function_exists("session")) {
    /**
     * Return the session binding.
     *
     * @return Session
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function session(): Session
    {
        return App::$app->session();
    }
}

if (!function_exists("db")) {
    /**
     * Return the database binding.
     *
     * @return Database
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function db(): Database
    {
        return app()->db();
    }
}

if (!function_exists("request")) {
    /**
     * Return the request binding.
     *
     * @param string|null $prop The key name to fetch from $_POST, $_FILES or $_GET.
     * @return mixed|Request|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function request($prop = null)
    {
        if ($prop != null) {
            return App::$app->request()->$prop;
        }

        return App::$app->request();
    }
}

if (!function_exists("collection")) {
    /**
     * Return a new empty collection.
     *
     * @param array $array
     * @return Collection
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function collection($array = []): Collection
    {
        return new Collection($array);
    }
}

if (!function_exists("response")) {
    /**
     * Return the response binding, or a new response instance.
     *
     * @param ...$params
     * @return Response
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function response(...$params): Response
    {
        if (!empty($params)) {
            return new Response(...$params);
        }

        return App::$app->response();
    }
}

if (!function_exists("redirect")) {
    /**
     * Redirect the user to a specific location.
     *
     * @param $url
     * @param int $code
     * @return mixed
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function redirect($url, $code = 302)
    {
        return app()->response()->redirect($url, $code);
    }
}

if (!function_exists("isCLI")) {
    /**
     * Determine if the app is running in CLI.
     *
     * @return bool
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function isCLI(): bool
    {
        if (server('APP_ENV') == 'testing') {
            if (server("CLI") == 'false') {
                return false;
            }
        }

        return php_sapi_name() === 'cli';
    }
}

if (!function_exists("headers")) {
    /**
     * Get all headers sent by the client.
     *
     * @return array|false
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function headers()
    {
        return function_exists("getallheaders") ? getallheaders() : [];
    }
}

if (!function_exists("server")) {
    /**
     * Get data from $_SERVER.
     *
     * @param $key
     * @param null $value
     * @return mixed|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function server($key, $value = null)
    {
        if ($value !== null)
            $_SERVER[$key] = $value;
        else
            return $_SERVER[$key] ?? null;
    }
}

if (!function_exists("app")) {
    /**
     * Return the CLI or CGI app instance.
     *
     * @return \OSN\Framework\Foundation\App
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function app()
    {
        if (isCLI()) {
            return \OSN\Framework\Console\App::$app;
        }
        else {
            return App::$app;
        }
    }
}

if (!function_exists("basepath")) {
    /**
     * Return the absolute path of a file according to the application root.
     *
     * @param string $path
     * @return string
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function basepath($path = ''): string
    {
        return app()->config["root_dir"] . $path;
    }
}

if (!function_exists("view_exists")) {
    /**
     * Check if a view exists.
     *
     * @param $view
     * @return bool
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function view_exists($view): bool
    {
        return file_exists(basepath('/resources/views/') . str_replace('.', '/', $view) . '.php');
    }
}

if (!function_exists("abort")) {
    /**
     * Abort the application.
     *
     * @param int $code
     * @param string|null $message
     * @param array $headers
     * @return bool
     * @throws \OSN\Framework\Exceptions\HTTPException
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function abort(int $code = 500, ?string $message = null, array $headers = []): bool
    {
        throw new \OSN\Framework\Exceptions\HTTPException($code, $message == null ?? Response::getStatusFromCode($code), $headers);
    }
}

if (!function_exists("is_collection")) {
    /**
     * Check if the given value is a collection.
     *
     * @param $collect
     * @return bool
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function is_collection($collect): bool
    {
        return ($collect instanceof Collection);
    }
}


if (!function_exists("is_model")) {
    /**
     * Check if the given value is a model.
     *
     * @param $model
     * @return bool
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function is_model($model): bool
    {
        return $model instanceof Model;
    }
}

if (!function_exists("test_env")) {
    /**
     * Check if the application is under testing.
     *
     * @return bool
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function test_env(): bool
    {
        return is_file(basepath("/var/test-lock"));
    }
}

if (!function_exists("now")) {
    /**
     * Return current time as a Carbon instance.
     *
     * @param null $tz
     * @return Carbon
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function now($tz = null): Carbon
    {
        return Carbon::now($tz);
    }
}

if (!function_exists("is_jsonable")) {
    /**
     * Determine if the given value can be converted to JSON.
     *
     * @param $value
     * @return bool
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function is_jsonable($value): bool
    {
        return is_array($value) || is_object($value) || $value instanceof JsonSerializable;
    }
}

if (!function_exists("env")) {
    /**
     * Get an environment configuration variable value.
     *
     * @param $env
     * @return mixed|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function env($env)
    {
        return app()->env[$env] ?? null;
    }
}

if (!function_exists("config")) {
    /**
     * Get the whole config or individual configs.
     *
     * @param null $c
     * @return mixed|\OSN\Framework\Core\Config|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function config($c = null)
    {
        if ($c === null) {
            return app()->config;
        }

        return app()->config->$c ?? null;
    }
}

if (!function_exists("tmp_dir")) {
    /**
     * Return the temporary file directory of the app.
     *
     * @return mixed|string
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function tmp_dir()
    {
        return app()->env["TMP_DIR"] ?? (app()->config('root_dir') . '/var/tmp/');
    }
}

if (!function_exists("cache_dir")) {
    /**
     * Return the cache file directory of the app.
     *
     * @return mixed|string
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function cache_dir()
    {
        return app()->env["CACHE_DIR"] ?? (app()->config('root_dir') . '/var/cache/');
    }
}

if (!function_exists("view")) {
    /**
     * Return a new view instance.
     *
     * @param string $name
     * @param array $data
     * @param string $layout
     * @return View
     * @throws \OSN\Framework\Exceptions\FileNotFoundException
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function view(string $name, array $data = [], $layout = ''): View
    {
        return new View($name, $data, $layout);
    }
}

if (!function_exists("rrmdir")) {
    /**
     * Remove a directory recursively.
     *
     * @param $dir
     * @link https://www.php.net/manual/en/function.rmdir.php#117354
     */
    function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                        rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($dir. DIRECTORY_SEPARATOR .$object);
                }
            }
            rmdir($dir);
        }
    }
}

if (!function_exists("rrmdir_contents")) {
    /**
     * Remove directory contents.
     *
     * @param $dir
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function rrmdir_contents($dir) {
        if (!is_dir($dir))
            return;

        $objects = scandir($dir);
        $cwd = getcwd();
        chdir($dir);

        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (is_dir($object)) {
                    rrmdir($object);
                }

                if (is_file($object)) {
                    unlink($object);
                }
            }
        }

        chdir($cwd);
    }
}


if (!function_exists('csrf_token')) {
    /**
     * Get a new CSRF token.
     *
     * @return string
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function csrf_token(): string
    {
        return app()->csrf->new();
    }
}


if (!function_exists('end_csrf_token')) {
    /**
     * Destroy the CSRF tokens.
     *
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function end_csrf_token()
    {
        app()->csrf->endCSRF();
    }
}

if (!function_exists('println')) {
    /**
     * Print a new line with the given text.
     *
     * @param $value
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function println($value)
    {
        echo $value . "\n";
    }
}

if (!function_exists('errors')) {
    /**
     * Get the validation errors for the given field.
     *
     * @param $field
     * @return mixed|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function errors($field)
    {
        if (!isset($GLOBALS['__validation_errors']))
            $GLOBALS['__validation_errors'] = session()->getFlash('__validation_errors');

        return $GLOBALS['__validation_errors'][$field] ?? null;
    }
}

if (!function_exists('old')) {
    /**
     * Get the previously submitted data.
     *
     * @param null $key
     * @return array|mixed|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function old($key = null)
    {
        return request()->old($key);
    }
}

if (!function_exists('route')) {
    /**
     * Get a route with name.
     *
     * @param $name
     * @param mixed ...$args The route parameter values
     * @return Route|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function route($name, ...$args): ?Route
    {
        /**
         * @var Route $route
         */
        $route = App::$app->router->findByLogic(function (Route $route) use ($name) {
            $cond = true;
            return $route->name() == $name && $cond;
        });

        if(!$route instanceof Route)
            return null;

        $path = $route->path();

        if (preg_match_all("/(\(.*?\))/", $route->path(), $matches)) {
            array_shift($matches);

            if (count($matches) === 1)
                $matches = $matches[0];

            foreach ($matches as $i => $match) {
                $path = preg_replace('/' . preg_quote($match, '/') . '/', $args[$i] ?? '', $path, 1);
            }
        }

        return new Route($route->method(), $path, $route->action(), $route->name(), $route->middleware());
    }
}

if (!function_exists('error_first')) {
    /**
     * Get the first validation error of a field.
     *
     * @param $field
     * @return mixed|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function error_first($field)
    {
        $errors = errors($field);

        if ($errors == null) {
            return null;
        }

        $value = null;

        foreach ($errors as $error) {
            $value = $error;
            break;
        }

        return $value;
    }
}

if (!function_exists('fire')) {
    /**
     * Fire an event.
     *
     * @throws EventException
     */
    function fire($event, ?callable $handler = null, array $data = [])
    {
        if (is_string($event))
            $event = new $event($data);

        if (!$event instanceof Event) {
            throw new EventException("Event " . get_class($event) . " does not implement " . Event::class);
        }

        if ($handler !== null)
            $event->setHandler($handler);

        $data = $event->fireHandlers();
        $event->setFired();

        return $data;
    }
}

if (!function_exists('to_array')) {
    /**
     * Convert objects tp array.
     *
     * @param Arrayable $arrayable
     * @return array
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function to_array(Arrayable $arrayable): array
    {
        return $arrayable->toArray();
    }
}

if (!function_exists('elapsed_time')) {
    /**
     * Get the elapsed time from the app start.
     *
     * @return float
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function elapsed_time()
    {
        return microtime(true) - APP_START;
    }
}

if (!function_exists('get_base_class')) {
    /**
     * Get the base class name from the whole class (with namespaces)
     *
     * @param string $class
     * @return string
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function get_base_class(string $class): string
    {
        $array = explode("\\", $class);
        return end($array);
    }
}















