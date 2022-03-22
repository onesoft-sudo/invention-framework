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

/**
 * The session manager.
 *
 * @package OSN\Framework\Core
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Session
{
    /**
     * The session keys that needs to be removed after the response is sent.
     *
     * @var array
     */
    protected array $purge = [];

    /**
     * Session constructor.
     *
     * @return void
     */
    public function __construct()
    {
        @session_start();
    }

    /**
     * @deprecated
     */
    public function setFromModel(Model $model, array $excludedFields = [])
    {
        foreach ($model->get() as $field => $value) {
            if (in_array($field, $excludedFields)) {
                continue;
            }

            $this->set($field, $value);
        }
    }

    /**
     * @deprecated
     */
    public function unsetFromModel(Model $model, array $excludedFields = [])
    {
        foreach ($model->get() as $field => $value) {
            if (in_array($field, $excludedFields)) {
                continue;
            }

            $this->unset($field);
        }
    }

    /**
     * @deprecated
     */
    public function setModel(string $key, Model $model)
    {
        $_SESSION[$key] = serialize($model);
    }

    /**
     * @deprecated
     */
    public function getModel(string $key)
    {
        return unserialize($_SESSION[$key] ?? null);
    }

    /**
     * Get a session key value.
     *
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Set a session key.
     *
     * @param $key
     * @return void
     */
    public function unset($key)
    {
        $_SESSION[$key] = null;
        unset($_SESSION[$key]);
    }

    /**
     * Determine if the session key exists.
     *
     * @param $key
     * @return bool
     */
    public function isset($key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Get a flash data.
     *
     * @param null $key
     * @return mixed|null
     */
    public function getFlash($key = null)
    {
        $key = $key ?? "flash_message";
        $msg = $this->get($key);

        if ($msg === null)
            return null;

        $this->purge[] = $key;

        return $msg;
    }

    /**
     * Set a flash data.
     *
     * @param string $key
     * @param $value
     * @todo Update this method [PURGING]
     */
    public function setFlash(string $key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Destroy the session.
     *
     * @return void
     */
    public function destroy()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Destruct the object and remove unneeded session keys.
     *
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->purge as $key) {
            $this->unset($key);
        }
    }
}
