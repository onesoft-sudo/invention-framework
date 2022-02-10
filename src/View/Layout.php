<?php


namespace OSN\Framework\View;


use OSN\Framework\Core\App;
use OSN\Framework\Exceptions\FileNotFoundException;
use OSN\Framework\PowerParser\PowerParser;

class Layout
{
    protected string $name;
    protected $title;
    protected array $_names = [];
    protected array $_sections = [];
    protected array $_names_modified = [];

    /**
     * Layout constructor.
     */
    public function __construct(string $name, $title = '', array $conf = [])
    {
        $this->name = str_replace('.', '/', $name);
        $this->title = $title;
        $this->_sections = $conf['sections'] ?? [];
        $this->_names = $conf['names'] ?? [];
        $this->_names_modified = $conf['names_modified'] ?? [];
    }

    /**
     * @throws FileNotFoundException
     */
    public function getContents()
    {
        $file = App::$app->config["root_dir"] . "/resources/views/" . $this->name . ".php";

        if (!is_file($file)) {
            $isPower = true;
            $file = App::$app->config["root_dir"] . "/resources/views/" . $this->name . ".power.php";
        }

        if (!is_file($file)) {
            throw new FileNotFoundException("Couldn't find the specified layout '{$this->name}': No such file or directory");
        }

        if(isset($isPower)) {
            $power = new PowerParser($file);
            $file = ($power)()['file'];
        }

        $_title = $this->title;

        $_names = $this->_names;
        $_sections = $this->_sections;
        $_names_modified = $this->_names_modified;

        ob_start();
        include $file;
        $out = ob_get_clean();

        //if (isset($isPower))
         //   unlink($file);

        return $out;
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
