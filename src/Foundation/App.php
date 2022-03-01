<?php


namespace OSN\Framework\Foundation;


use Dotenv\Dotenv;
use OSN\Envoy\Envoy;
use OSN\Framework\Cache\Cache;
use OSN\Framework\Container\Container;
use OSN\Framework\Core\Config;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Initializable;
use OSN\Framework\Events\BuiltIn\AppRunCompleteEvent;
use OSN\Framework\Events\TriggersEvent;

abstract class App extends Container
{
    use Initializable, TriggersEvent;

    public static self $app;
    public Cache $cache;
    public Config $config;
    public Database $db;

    public array $env = [];

    public function __construct(string $rootpath, array $env = [])
    {
        if (server('APP_TESTING') == '1') {
            $_ENV = $env;
        }
        else {
            (new Envoy($rootpath . '/.env'))->load();
        }

        $this->env = $_ENV;
        self::$app = $this;
        $this->config = new Config($rootpath . $this->env['CONF_FILE']);
        $this->config->root_dir = $rootpath;
        $this->loadInitializers();
        $this->preinit();

        $this->cache = new Cache($rootpath . '/var/cache');
        $this->db = new Database($this->env);

        $this->bindings[Cache::class] = [
            'callback' => fn() => $this->cache,
            'once' => true,
            'prop' => 'cache'
        ];

        $this->bindings[Database::class] = [
            'callback' => fn() => $this->db,
            'once' => true,
            'prop' => 'db'
        ];

        $this->loadBindingsFromConfig();
        $this->boot();
        $this->init();
    }

    public function boot()
    {

    }

    public function run()
    {
        static::dispatch(AppRunCompleteEvent::class, [
            'app' => $this
        ]);
    }
}
