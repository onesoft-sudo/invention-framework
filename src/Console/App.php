<?php


namespace OSN\Framework\Console;

use Dotenv\Dotenv;
use OSN\Framework\Cache\Cache;
use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Config;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Initializable;
use OSN\Framework\Events\BuiltIn\AppRunCompleteEvent;
use OSN\Framework\Events\TriggersEvent;
use OSN\Framework\Exceptions\CommandNotFoundException;
use Symfony\Component\Console\Application;

class App extends \OSN\Framework\Foundation\App
{
    public Arguments $argument;
    public Generator $generator;
    public Commander $commander;

    public array $argv;
    public Application $symfonyApp;

    /**
     * @throws \Exception
     */
    public function boot()
    {
        global $argv;

        $this->argv = $argv;
        $this->db = new Database($this->env);
        $this->argument = new Arguments();
        $this->generator = new Generator();
        $this->commander = new Commander($this->argument);
        $this->symfonyApp = new Application();
    }

    public static function config($key)
    {
        return self::$app->config[$key] ?? false;
    }

    public static function env(): array
    {
        return self::$app->env;
    }

    public static function db(): Database
    {
        return self::$app->db;
    }

    public function add(\Symfony\Component\Console\Command\Command $cmd): ?\Symfony\Component\Console\Command\Command
    {
        return $this->symfonyApp->add($cmd);
    }

    public function run($input = null, $output = null)
    {
        try {
            $this->afterinit();

            if (!isCLI()) {
                echo 'This application must be invoked with PHP CLI.\n';
                exit(-1);
            }

            $this->symfonyApp->run($input, $output);
        }
        catch (\Throwable $e) {
            echo "\033[1;31m" . get_class($e) . "\033[0m: " . $e->getMessage() . " \033[1;33m(Code " . $e->getCode() . ")\033[0m\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
            exit($e->getCode());
        }
        finally {
            parent::run();
        }
    }
}
