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

namespace OSN\Framework\RateLimiting;


use OSN\Framework\Core\Middleware;
use OSN\Framework\Exceptions\HTTPException;
use OSN\Framework\Http\Request;

class RateLimiter extends Middleware
{
    protected int $limit;
    protected int $sec;

    protected object $data;
    protected string $confDB = '/storage/invention/ratelimiter.json';

    public function __construct(int $limit = 1, int $sec = 2)
    {
        $this->limit = $limit;
        $this->sec = $sec;
        $this->data = $this->loadData();
    }

    protected function loadData()
    {
        return json_decode(file_get_contents(basepath($this->confDB)));
    }

    protected function updateData()
    {
        file_put_contents(basepath($this->confDB), json_encode($this->data, JSON_PRETTY_PRINT));
    }

    protected function findClientByIP(string $ip)
    {
        $client = null;

        foreach ($this->data as $ip1 => &$datum) {
            if ($ip1 === $ip) {
                $client = &$datum;
                break;
            }
        }

        return $client;
    }

    #[\Pure]
    protected function getClientByIP(string $ip)
    {
        $client = $this->findClientByIP($ip);

        if ($client === null) {
            $client = (object) [
                "request_count" => 1,
                "last_request_time" => date(DATE_ATOM)
            ];
        }

        return $client;
    }

    protected function resetClientData(string $ip, object $client)
    {
        if ((strtotime($client->last_request_time) + $this->sec) <= time()) {
            unset($this->data->$ip);
        }
        else {
            $client->last_request_time = date(DATE_ATOM);
        }

        $this->updateData();
    }

    public function handle(Request $request)
    {
        $ip = $request->ip;
        $client = $this->getClientByIP($ip);

        $client->request_count++;
        $this->data->$ip = $client;

        if ($client->request_count >= $this->limit && (strtotime($client->last_request_time) + $this->sec) >= time()) {
            throw new HTTPException(429, "Too Many Requests", [
                "Retry-After" => date(DATE_RFC2822, strtotime($client->last_request_time) + $this->sec + 1)
            ]);
        }

        $this->resetClientData($ip, $client);
    }
}