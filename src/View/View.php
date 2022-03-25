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
use OSN\Framework\Facades\Request;
use OSN\Framework\PowerParser\PowerParser;

class View
{
    protected string $name;
    protected $layout;
    protected array $data;
    protected PowerParser $parser;
    protected string $file;
    protected string $title = '';
    protected array $slots = [];

    /**
     * View constructor.
     *
     * @throws FileNotFoundException
     */
    public function __construct(string $name, $data = [], $layout = '')
    {
        $this->name = str_replace('.', '/', $name);
        $this->layout = $layout === '' ? App::$app->config["layout"] : $layout;
        //$this->layout = $layout === null ? null : $layout;
        $this->data = $data;

        $file = App::$app->config["root_dir"] . "/resources/views/" . $this->name . ".php";

        if (!is_file($file)) {
            $file = App::$app->config["root_dir"] . "/resources/views/" . $this->name . ".power.php";
        }

        if (!is_file($file)) {
            throw new FileNotFoundException("Couldn't find the specified view '{$this->name}': No such file or directory");
        }

        $this->file = $file;
        $this->parser = new PowerParser($file);
    }

    /**
     * @param array $slots
     * @return View
     */
    public function setSlots(array $slots): self
    {
        $this->slots = $slots;
        return $this;
    }

    public function render()
    {
        $_names = [];
        $_sections = [];
        $_names_modified = [];
        $_layout = $this->layout;
        $_slots = $this->slots;

        $view = $this->renderView($_names, $_sections, $_names_modified, $_layout, $_slots);

        if ($_layout === false || $_layout === null)
             return $view;

        $layout = $_layout === null ? '[[ view ]]' : (new Layout($_layout, $this->title, [
            'sections' => $_sections,
            'names' => $_names,
            'names_modified' => $_names_modified,
        ]));

        return preg_replace("/\[\[( *)view( *)\]\]/", $view, $layout);
    }

    public function renderView(&$_names = [], &$_sections = [], &$_names_modified = [], &$_layout = [], &$_slots = [])
    {
        foreach ($this->data as $key => $value) {
            $$key = $value;
        }

        if (preg_match('/\.power\.php/', basename($this->file))) {
            $file = ($this->parser)()['file'];
        }
        else {
            $file = $this->file;
        }

        ob_start();
        include $file;
        $out = ob_get_clean();

        $this->title = $_title ?? '';

        return $out;
    }

    public function getURI()
    {
        return App::request()->baseURI;
    }

    public function __invoke()
    {
        return $this->render();
    }

    public function __toString()
    {
        return $this->render();
    }
}
