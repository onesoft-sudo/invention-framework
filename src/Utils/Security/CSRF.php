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

namespace OSN\Framework\Utils\Security;


use OSN\Framework\Core\App;
use OSN\Framework\Core\Session;
use OSN\Framework\Http\CSRFHelper;

class CSRF
{
    public const SESSION_CSRF_KEY = '__csrf_tokens';

    public function __construct(protected Session $session)
    {
        if (!$this->session->isset(static::SESSION_CSRF_KEY))
            $this->session->set(static::SESSION_CSRF_KEY, []);
    }

    protected function generate(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(20));
    }

    public function get()
    {
        return $this->getWithInfo()['token'];
    }

    public function set(array $tokens)
    {
        $this->session->set(static::SESSION_CSRF_KEY, $tokens);
    }

    public function add(string $token)
    {
        $this->session->push(static::SESSION_CSRF_KEY, ['date' => time(), 'token' => $token]);
    }

    public function endCSRF()
    {
        $this->session->unset(static::SESSION_CSRF_KEY);
    }

    public function prune()
    {
        $time = 30;
        $tokens = $this->session->get(static::SESSION_CSRF_KEY);
        $newTokens = [];

        foreach ($tokens as $token) {
            if (($token['date'] + $time) < time()) {
                continue;
            }

            $newTokens[] = $token;
        }

        $this->set($newTokens);
    }

    #[\Pure]
    public function isValid($token): bool
    {
        $tokens = $this->session->get(static::SESSION_CSRF_KEY);

        foreach ($tokens as $token1) {
            if ($token === $token1['token']) {
                return true;
            }
        }

        return false;
    }

    public function getWithInfo()
    {
        $array = $this->session->get(static::SESSION_CSRF_KEY);
        return end($array);
    }

    public function new(): string
    {
        $token = $this->generate();
        $this->add($token);
        return $token;
    }

    public function __destruct()
    {

    }
}
