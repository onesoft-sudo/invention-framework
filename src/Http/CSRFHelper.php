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

namespace OSN\Framework\Http;


use OSN\Framework\Core\App;

trait CSRFHelper
{
    public function generate(): string
    {
        return sha1(rand());
    }

    public function get()
    {
        return App::$app->session->get("__csrf_token");
    }

    public function set($token)
    {
        App::$app->session->set("__csrf_token", $token);
    }

    public function endCSRF()
    {
        App::$app->session->unset("__csrf_token");
    }

    public function new(): string
    {
        $token = $this->generate();
        $this->set($token);
        return $token;
    }
}
