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


trait HTTPRequestParser
{
    protected function getHost(string $hostname)
    {
        return explode(":", $hostname)[0];
    }

    protected function getPort(string $hostname)
    {
        $array = explode(":", $hostname);
        return end($array);
    }

    protected function getBaseURI(string $uri)
    {
        $pos = strpos($uri, "?");
        return substr($uri,0, $pos === false ? strlen($uri) : $pos);
    }

    protected function getQueryString(string $uri)
    {
        $pos = strpos($uri, "?");
        return substr($uri, $pos === false ? strlen($uri) : ($pos + 1));
    }
}
