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

namespace OSN\Framework\Core;


class Session
{
    protected array $purge = [];

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

        $this->purge[] = $key;

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

    public function __destruct()
    {
        foreach ($this->purge as $key) {
            $this->unset($key);
        }
    }
}
