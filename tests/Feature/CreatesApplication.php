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
        $this->root = __DIR__ . '/../TestApplication';
        $this->dbpath = $this->root . '/var/tmp/database.db';
        file_put_contents($this->dbpath, '');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

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

    protected function updateRequest()
    {
        $this->app->request->update();
        $this->app->request->uri = $this->route ?? $this->app->request->uri;
        $this->app->request->baseURI = $this->route ?? $this->app->request->baseURI;
        $this->app->request->method = $this->method ?? $this->app->request->method;
    }
}
