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

namespace OSN\Framework\Console;

use OSN\Framework\Cache\Cache;
use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Config;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Initializable;
use OSN\Framework\Events\BuiltIn\AppRunCompleteEvent;
use OSN\Framework\Events\TriggersEvent;
use OSN\Framework\Exceptions\CommandNotFoundException;
use Symfony\Component\Console\Application;

/**
 * The console application class.
 *
 * @package OSN\Framework\Console
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class App extends \OSN\Framework\Foundation\App
{
    /**
     * The command-line argument array.
     *
     * @var array
     */
    public array $argv;

    /**
     * The Symfony application instance.
     *
     * @var Application
     */
    public Application $symfonyApp;

    /**
     * Bootstrap the app services.
     *
     * @return void
     */
    public function boot()
    {
        global $argv;

        $this->argv = $argv;
        $this->symfonyApp = new Application();
    }

    /**
     * Add a command to the app.
     *
     * @param \Symfony\Component\Console\Command\Command $cmd
     * @return \Symfony\Component\Console\Command\Command|null
     */
    public function add(\Symfony\Component\Console\Command\Command $cmd): ?\Symfony\Component\Console\Command\Command
    {
        return $this->symfonyApp->add($cmd);
    }

    /**
     * Run the application.
     *
     * @param null $input
     * @param null $output
     */
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
