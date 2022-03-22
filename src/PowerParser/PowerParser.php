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

namespace OSN\Framework\PowerParser;


use OSN\Framework\Contracts\Component;
use OSN\Framework\Core\App;
use OSN\Framework\DataTypes\_String;

class PowerParser
{
    use ParseData;

    protected string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public static function cache_dir()
    {
        return cache_dir() . 'powerparser/';
    }

    protected function php(string $code): string
    {
        return "<?php $code ?>";
    }

    protected function replaceFromPregArray(string $str, array $arr, string $repl)
    {
        $out = $str;

        if (isset($arr[0][0]) && isset($arr[1][0])) {
            foreach ($arr[0] as $k => $value) {
                $out = str_replace($value, str_replace('%s', $arr[1][$k], $repl), $out);
            }
        }

        return $out;
    }

    protected function endblock(string $code, string $regex)
    {
        return preg_replace("/$regex/", $this->php("}\n"), $code);
    }

    public function replaceDirectivesWithPHPCode(string $code): string
    {
        $output = $code;
        $matches = [];

        /**
         * If, elseif, else and endif statements.
         */
        $regex = ":if\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('if (%s) {'));
        }

        $regex = ":elseif\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php("} elseif (%s) {"));
        }

        $regex = ":else:";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = preg_replace("/$regex/", $this->php("} else {"), $output);
        }

        $output = $this->endblock($output, ':endif:');

        /**
         * foreach and endforeach statements.
         * This must be parsed before for loop parsing.
         */
        $regex = ":foreach\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('foreach (%s) {'));
        }

        $output = $this->endblock($output, ':endforeach:');

        /**
         * for and endfor statements.
         */
        $regex = ":for\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('for (%s) {'));
        }

        $output = $this->endblock($output, ':endfor:');

        /**
         * php and endphp statements.
         */
        $output = preg_replace('/:php:/', "<?php\n", $output);
        $output = preg_replace('/:endphp:/', "\n?>", $output);

        /**
         * while and endwhile statements.
         */
        $regex = ":while\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('while (%s) {'));
        }

        $output = $this->endblock($output, ':endwhile:');

        /**
         * dowhile and enddowhile statements.
         */
        $output = preg_replace('/:dowhile:/', $this->php("do {\n"), $output);

        $regex = ":enddowhile\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php("}\nwhile (%s);"));
        }

        /**
         * title statement.
         */

        $regex = ":title\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php("\$_title = %s;"));
        }

        /**
         * section and endsection statements.
         */
        $regex = ":section\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('$_names[] = %s; $_names_modified[] = %s; ob_start();'));
        }

        $output = preg_replace('/:endsection:/', $this->php('$_sections[array_shift($_names_modified)] = ob_get_clean();'), $output);

        /**
         * yield statement.
         */
        $regex = ":yield\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('echo $_sections[%s] ?? "";'));
        }

        /**
         * extends statement.
         */
        $regex = ":extends\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('$_layout = %s;'));
        }

        /**
         * args statement.
         */
        $regex = ":args\(([0-9]+)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('echo $_component_args[%s] ?? "";'));
        }

        /**
         * :csrf statement.
         */
        $output = preg_replace('/:csrf:/', '<input type="hidden" name="__csrf_token" value="<?= csrf_token() ?>">', $output);

        /**
         * method statement.
         */
        $regex = ":method\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php('echo \OSN\Framework\View\Component::init("custom-httpmethod", %s);'));
        }

        /**
         * while and endwhile statements.
         */
        $regex = ":error\((.*?)\):";
        if (preg_match_all("/$regex/", $output, $matches)) {
            $output = $this->replaceFromPregArray($output, $matches, $this->php("\$_error_field = %s;\n if (error_first(\$_error_field)) { \$_error_current = error_first(\$_error_field); "));
        }

        $output = preg_replace("/:enderror:/", $this->php("unset(\$_error_field);\nunset(\$_error_current); \n}\n"), $output);

        /*
         * Component tag rendering.
         */
        $regex = "< *c-([a-z0-9-\.]+)( *([\n ]+[a-zA-Z0-9-_:\.@]+\=[\"](.*?)[\"])+)? *(\/)? *>";

        if (preg_match_all("/$regex/m", $output, $matches)) {
            foreach ($matches[1] as $i => $match) {
                preg_match_all("/([A-Za-z0-9-_:\.@]+)\=[\"](.*?)([\"])/", trim($matches[2][$i]), $matches2);
                $attributes = '';

                foreach ($matches2[0] as $k => $v) {
                    $bind = isset($matches2[1][$k][0]) && $matches2[1][$k][0] == ':';

                    if ($bind && $matches2[2][$k] == '') {
                        $attributes .= "\"" . substr($matches2[1][$k], 1) . "\" => null,";
                        continue;
                    }

                    $attributes .= preg_replace("/:?([A-Za-z0-9-_:\.@]+)\=[\"](.*?)[\"]/", "\"" . ($bind ? substr($matches2[1][$k], 1) : "$1") . "\" => " . ($bind ? '$2' : "\"\$2\"") . ",", $v);
                }

                $attributes = "[$attributes]";

                $output = str_replace($matches[0][$i], $this->php("echo \\OSN\\Framework\\PowerParser\\PowerParser::renderComponent('$match', " . ($attributes) . ");"), $output);
            }
        }

        return $output;
    }

    /**
     * Render a component.
     *
     * @param string $htmlTag
     * @param array $attributes
     * @return Component
     */
    public static function renderComponent(string $htmlTag, array $attributes)
    {
        $class = "\\App\\ViewComponents\\" . _String::from($htmlTag)->slug2className()->replace('/\./', "\\");
        return new $class($attributes);
    }

    protected function eval($code)
    {
        return eval($code);
    }

    public function parse(string $code): string
    {
        $output = $code;
        $output = $this->replaceDirectivesWithPHPCode($output);
        $replacements = $this->replacements();

        foreach ($replacements as $str => $replacement) {
            $output = preg_replace("/$str/", $replacement, $output);
        }

        return $output;
    }

    public function compile(): array
    {
        if (!is_dir(self::cache_dir()))
            mkdir(self::cache_dir(), 0755, true);

        $tmpfile = self::cache_dir() . sha1_file($this->file) . '.php';

        if (is_file($tmpfile)) {
            return ['file' => $tmpfile, 'content' => null];
        }
        else {
            $content = file_get_contents($this->file);
            $parsed = $this->parse($content);
            file_put_contents($tmpfile, $parsed);
        }

        return ['file' => $tmpfile, 'content' => $parsed];
    }

    public function __invoke(): array
    {
        return $this->compile();
    }
}
