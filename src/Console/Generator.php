<?php


namespace OSN\Framework\Console;


class Generator
{
    public function generate($path, $template, $call1, $call2, ...$args)
    {
        $template = $this->getTemplate($template);
        $content = sprintf($template, ...$args);

        if (file_exists($path)) {
            $call2("File exists");
            exit(-1);
        }

        file_put_contents($path, $content);
        $call1();
    }

    public function getTemplate($t)
    {
        $file = App::config('root_dir') . "/resources/templates/{$t}.template";
        return file_get_contents($file);
    }
}
