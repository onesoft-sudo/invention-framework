<?php


namespace OSN\Framework\Core;


use App\Events\AppRunningCompleteEvent;
use App\Events\JobEvent;
use Dotenv\Dotenv;
use OSN\Framework\Cache\Cache;
use OSN\Framework\Events\BuiltIn\AppRunCompleteEvent;
use OSN\Framework\Events\TriggersEvent;
use OSN\Framework\Exceptions\HTTPException;
use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;
use OSN\Framework\Routing\Router;
use OSN\Framework\View\View;

/**
 * Class App
 *
 * @package App\Core
 */
class App extends \OSN\Framework\Foundation\App
{
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;

    /**
     * @throws \Exception
     */
    public function boot()
    {
        $this->session = new Session();
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);

        $this->bindings[Request::class] = [
            'callback' => fn() => $this->request,
            'once' => true,
            'prop' => 'request'
        ];

        $this->bindings[Response::class] = [
            'callback' => fn() => $this->response,
            'once' => true,
            'prop' => 'response'
        ];

        $this->bindings[Config::class] = [
            'callback' => fn() => $this->config,
            'once' => true,
            'prop' => 'config'
        ];

        $this->bindings[Session::class] = [
            'callback' => fn() => $this->session,
            'once' => true,
            'prop' => 'session'
        ];

        $this->bindings[Router::class] = [
            'callback' => fn() => $this->router,
            'once' => true,
            'prop' => 'router'
        ];
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
