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

namespace OSN\Framework\View;


use OSN\Framework\Core\App;
use OSN\Framework\Exceptions\FileNotFoundException;
use OSN\Framework\PowerParser\PowerParser;

class Component
{
    protected string $name;
    protected array $_args = [];

    /**
     * Layout constructor.
     */
    public function __construct(string $name, array $conf = [])
    {
        $this->name = str_replace('.', '/', $name);
        $this->_args = $conf['args'] ?? [];
    }

    /**
     * @throws FileNotFoundException
     */
    public function getContents()
    {
        $file = App::$app->config["root_dir"] . "/resources/views/components/" . $this->name . ".php";

        if (!is_file($file)) {
            $isPower = true;
            $file = App::$app->config["root_dir"] . "/resources/views/components/" . $this->name . ".power.php";
        }

        if (!is_file($file)) {
            throw new FileNotFoundException("Couldn't find the specified component '{$this->name}': No such file or directory");
        }

        if(isset($isPower)) {
            $power = new PowerParser($file);
            $file = ($power)()['file'];
        }

        $_component_args = $this->_args;

        ob_start();
        include $file;
        $out = ob_get_clean();

        return $out;
    }

    public static function init(string $name, ...$args)
    {
        $comp = new static($name, ['args' => $args]);
        return $comp->getContents();
    }

    public function getName()
    {
        return $this->name;
    }

    public function __invoke()
    {
        return $this->getContents();
    }

    public function __toString()
    {
        return $this->getContents();
    }
}
