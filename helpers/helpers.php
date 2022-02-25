<?php

use Carbon\Carbon;
use OSN\Framework\Contracts\Event;
use OSN\Framework\Core\Model;
use OSN\Framework\Exceptions\EventException;
use OSN\Framework\Routing\Route;
use OSN\Framework\Utils\Arrayable;
use OSN\Framework\Utils\Security\Auth;
use OSN\Framework\Core\App;
use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Session;
use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;
use OSN\Framework\Utils\Security\CSRF;
use OSN\Framework\View\View;

if (!function_exists("auth")) {
    function auth(): Auth
    {
        return new Auth();
    }
}

if (!function_exists("ddr")) {
    function ddr($param)
    {
        echo "<pre>";
        var_dump($param);
        echo "</pre>";
        exit();
    }
}

if (!function_exists("dp")) {
    function dp($param)
    {
        echo "<pre>";
        print_r($param);
        echo "</pre>";
        exit();
    }
}

if (!function_exists("de")) {
    function de($param)
    {
        echo "<pre>";
        echo($param);
        echo "</pre>";
        exit();
    }
}

if (!function_exists("session")) {
    function session(): Session
    {
        return App::$app->session();
    }
}

if (!function_exists("db")) {
    function db(): Database
    {
        return app()->db();
    }
}

if (!function_exists("request")) {
    /**
     * @param null $prop
     * @return mixed|Request|null
     * @author Ar Rakin <rakinar2@gmail.com>
     */
    function request($prop = null)
    {
        if ($prop != null) {
            if (App::request()->isWriteRequest())
                return App::request()->post->$prop ?? '';
            else
                return App::request()->get->$prop ?? '';
        }

        return App::$app->request();
    }
}

if (!function_exists("collection")) {
    function collection($array = []): Collection
    {
        return new Collection($array);
    }
}

if (!function_exists("response")) {
    function response(...$params): Response
    {
        if (!empty($params)) {
            return new Response(...$params);
        }

        return App::$app->response();
    }
}

if (!function_exists("redirect")) {
    function redirect($url, $code = 302)
    {
        return app()->response()->redirect($url, $code);
    }
}

if (!function_exists("isCLI")) {
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
    function headers()
    {
        return function_exists("getallheaders") ? getallheaders() : [];
    }
}

if (!function_exists("server")) {
    function server($key, $value = null)
    {
        if ($value !== null)
            $_SERVER[$key] = $value;
        else
            return $_SERVER[$key] ?? null;
    }
}

if (!function_exists("app")) {
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
    function basepath($path = ''): string
    {
        return app()->config["root_dir"] . $path;
    }
}

if (!function_exists("view_exists")) {
    function view_exists($view): bool
    {
        return file_exists(basepath('/resources/views/') . str_replace('.', '/', $view) . '.php');
    }
}

if (!function_exists("abort")) {
    function abort(int $code = 500, ?string $message = null, array $headers = []): bool
    {
        throw new \OSN\Framework\Exceptions\HTTPException($code, $message == null ?? Response::getStatusFromCode($code), $headers);
    }
}

if (!function_exists("is_collection")) {
    function is_collection($collect): bool
    {
        return ($collect instanceof Collection);
    }
}


if (!function_exists("is_model")) {
    function is_model($model): bool
    {
        return $model instanceof Model;
    }
}

if (!function_exists("test_env")) {
    function test_env(): bool
    {
        return is_file(basepath("/var/test-lock"));
    }
}

if (!function_exists("now")) {
    function now($tz = null): Carbon
    {
        return Carbon::now($tz);
    }
}

if (!function_exists("is_jsonable")) {
    function is_jsonable($value): bool
    {
        return is_array($value) || is_object($value) || $value instanceof JsonSerializable;
    }
}

if (!function_exists("env")) {
    function env($env)
    {
        return app()->env[$env] ?? null;
    }
}

if (!function_exists("config")) {
    function config($c = null)
    {
        if ($c === null) {
            return app()->config;
        }

        return app()->config->$c ?? null;
    }
}

/**
 * @todo
 */

if (!function_exists("tmp_dir")) {
    function tmp_dir()
    {
        return app()->env["TMP_DIR"] ?? (app()->config('root_dir') . '/var/tmp/');
    }
}

if (!function_exists("cache_dir")) {
    function cache_dir()
    {
        return app()->env["CACHE_DIR"] ?? (app()->config('root_dir') . '/var/cache/');
    }
}

if (!function_exists("view")) {
    function view(string $name, array $data = [], $layout = ''): View
    {
        return new View($name, $data, $layout);
    }
}

if (!function_exists("rrmdir")) {
    /**
     * Taken from <https://www.php.net/manual/en/function.rmdir.php#117354>.
     *
     * @param $dir
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
    function csrf_token(): string
    {
        $csrf = new CSRF();
        return $csrf->new();
    }
}


if (!function_exists('end_csrf_token')) {
    function end_csrf_token()
    {
        $csrf = new CSRF();
        $csrf->endCSRF();
    }
}

if (!function_exists('println')) {
    function println($value)
    {
        echo $value . "\n";
    }
}

if (!function_exists('errors')) {
    function errors($field)
    {
        if (!isset($GLOBALS['__validation_errors']))
            $GLOBALS['__validation_errors'] = session()->getFlash('__validation_errors');

        return $GLOBALS['__validation_errors'][$field] ?? null;
    }
}

if (!function_exists('old')) {
    function old($key = null)
    {
        return request()->old($key);
    }
}

if (!function_exists('route')) {
    function route($name, ...$args): ?Route
    {
        /**
         * @var Route $route
         */
        $route = App::$app->router->findByLogic(function (Route $route) use ($name) {
            $cond = true;
//
//            if ($method != null) {
//                $cond = $route->method() == $method;
//            }

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
    function to_array(Arrayable $arrayable): array
    {
        return $arrayable->toArray();
    }
}

if (!function_exists('elapsed_time')) {
    function elapsed_time()
    {
        return microtime(true) - APP_START;
    }
}















