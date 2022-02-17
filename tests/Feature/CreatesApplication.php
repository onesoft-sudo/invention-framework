<?php

namespace OSN\Framework\Tests\Feature;

use OSN\Framework\Core\App;

trait CreatesApplication
{
    protected ?App $app;
    protected string $root;
    protected string $dbpath;

    public function createApp()
    {
//        App::$app = new class() extends App {
//            public \OSN\Framework\Routing\Router $router;
//            public \OSN\Framework\Http\Request $request;
//            public \OSN\Framework\Http\Response $response;
//
//            public function __construct()
//            {
//                $this->request = new Request();
//                $this->response = new Response();
//                $this->router = new Router($this->request, $this->response);
//            }
//        };

        $this->root = __DIR__ . '/../TestApplication';
        $this->dbpath = $this->root . '/var/tmp/database.db';
        file_put_contents($this->dbpath, '');

        $this->app = new App($this->root, [
            'APP_ENV' => 'dev',
            'CONF_FILE' => 'config/main.php',
            'SERVER_PORT' => '80',
            'DB_VENDOR' => 'sqlite',
            'DB_NAME' => realpath($this->dbpath)
        ]);
    }

    public function destroyApp()
    {
        $this->app->db->pdo = null;
        unlink($this->dbpath);
    }
}
