<?php


namespace OSN\Framework\Core;


class Session
{
    public function __construct()
    {
        @session_start();
    }

    public function setFromModel(Model $model, array $excludedFields = [])
    {
        foreach ($model->get() as $field => $value) {
            if (in_array($field, $excludedFields)) {
                continue;
            }

            $this->set($field, $value);
        }
    }

    public function unsetFromModel(Model $model, array $excludedFields = [])
    {
        foreach ($model->get() as $field => $value) {
            if (in_array($field, $excludedFields)) {
                continue;
            }

            $this->unset($field);
        }
    }

    public function setModel(string $key, Model $model)
    {
        $_SESSION[$key] = serialize($model);
    }

    public function getModel(string $key)
    {
        return unserialize($_SESSION[$key] ?? null);
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function unset($key)
    {
        $_SESSION[$key] = null;
        unset($_SESSION[$key]);
    }

    public function isset($key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function getFlash($key = null)
    {
        $key = $key ?? "flash_message";
        $msg = $this->get($key);

        if ($msg === null)
            return null;

        $this->unset($key);
        return $msg;
    }

    public function setFlash(string $key, $value)
    {
        $this->set($key, $value);
    }

    public function destroy()
    {
        session_unset();
        session_destroy();
    }
}
