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

namespace OSN\Framework\Foundation;


use OSN\Envoy\Envoy;
use OSN\Framework\Cache\Cache;
use OSN\Framework\Container\Container;
use OSN\Framework\Core\Config;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Initializable;
use OSN\Framework\Events\BuiltIn\AppRunCompleteEvent;
use OSN\Framework\Events\TriggersEvent;

/**
 * The base application class.
 *
 * @package OSN\Framework\Foundation
 * @author Ar Rakin <rakinar2@gmail.com>
 * @property Config $config
 * @property Cache $cache
 * @property Database $db
 */
abstract class App extends Container
{
    use Initializable, TriggersEvent;

    /**
     * The current application instance.
     *
     * @var App|$this
     */
    public static self $app;

    /**
     * The environment variables.
     *
     * @var array
     */
    public array $env = [];

    /**
     * App constructor.
     *
     * @param string $rootpath
     * @param array $env
     */
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

        $this->bind(Config::class, function() use ($rootpath) {
            $config = new Config($rootpath . $this->env['CONF_FILE']);
            $config->root_dir = $rootpath;
            return $config;
        }, 'config', true);

        $this->loadInitializers();
        $this->preinit();

        $this->bind(Cache::class, fn() => new Cache($rootpath . '/var/cache'), 'cache', true);
        $this->bind(Database::class, fn() => new Database($this->env), 'db', true);
        $this->bind(static::class, fn() => $this, 'instance', true);

        $this->loadBindingsFromConfig();
        $this->boot();
        $this->init();
    }

    /**
     * Get the current app instance.
     *
     * @return App
     */
    public static function getInstance(): App
    {
        return static::$app;
    }

    /**
     * Bootstrap the app services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Get a config variable value.
     *
     * @param $key
     * @return false|mixed
     */
    public static function config($key)
    {
        return static::$app->config[$key] ?? false;
    }

    /**
     * Get the database component instance.
     *
     * @return Database
     */
    public static function db(): Database
    {
        return static::$app->db;
    }

    /**
     * Get the environment variables.
     *
     * @return array
     */
    public static function env(): array
    {
        return static::$app->env;
    }

    /**
     * Run the application.
     *
     * @throws \OSN\Framework\Exceptions\EventException
     */
    public function run()
    {
        static::dispatch(AppRunCompleteEvent::class, [
            'app' => $this
        ]);
    }
}
