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

namespace OSN\Framework\Testing;


use OSN\Framework\Http\Request;
use OSN\Framework\Http\Response;

trait HTTPRequests
{
    /** @var static \OSN\Framework\Core\App $app */

    /**
     * @param $method
     * @param $uri
     * @param string $version
     * @param array $headers
     * @param array $params
     * @return false|string
     */
    public function createRequest($method, $uri, $version = "1.1", $headers = [], $params = [])
    {
        $headers_base = [
            "Connection" => "Close"
        ];

        if ($method != 'GET' && count($params) > 0)
            $headers_base["Content-Type"] = "application/x-www-form-urlencoded";

        $headers_new = array_merge($headers_base, $headers);
        $headers_str = "$method $uri HTTP/$version\r\n";

        foreach ($headers_new as $header => $value) {
            $headers_str .= "$header: $value\r\n";
        }

        $content = '';

        foreach ($params as $param => $value) {
            $content .= "$param=" . urlencode($value) . "&";
        }

        if ($method != 'GET' && count($params) > 0)
            $headers_str .= "Content-Length: " . (strlen($content) - 1) . "\r\n";

        $headers_str .= "\r\n";
        $headers_str .= $content;

        return count($params) > 0 ? substr($headers_str, 0, strlen($headers_str) - 1) : $headers_str;
    }

    public function sendRequest(string $request)
    {
        $socket = $this->socket();
        fputs($socket, $request);
        $socketdata = fgets($socket);

        while(!feof($socket)){
            $socketdata .= fgets($socket);
            if (preg_match("/\r\n\r\n/", $socketdata))
                break;
        }

        fclose($socket);
        return $socketdata;
    }

    public function sendGET($uri, $params = [], $headers = [], $version = "1.1")
    {
        return $this->sendRequest($this->createRequest("GET", $uri, $version, $headers, $params));
    }

    public function sendPOST($uri, $params = [], $headers = [], $version = "1.1")
    {
        return $this->sendRequest($this->createRequest("POST", $uri, $version, $headers, $params));
    }

    public function sendPUT($uri, $params = [], $headers = [], $version = "1.1")
    {
        return $this->sendRequest($this->createRequest("PUT", $uri, $version, $headers, $params));
    }

    public function sendDELETE($uri, $params = [], $headers = [], $version = "1.1")
    {
        return $this->sendRequest($this->createRequest("DELETE", $uri, $version, $headers, $params));
    }

    protected function socket()
    {
        return fsockopen("localhost", env('SERVER_PORT'));
    }

    protected array $_GET;
    protected array $_POST;
    protected array $_REQUEST;
    protected array $_SERVER;

    protected function boot()
    {
        $this->_GET = $_GET;
        $this->_POST = $_POST;
        $this->_REQUEST = $_REQUEST;
        $this->_SERVER = $_SERVER;
    }


    public function add($method, $uri, $data = [])
    {
        $this->boot();

        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;


        foreach ($data as $k => $datum) {
            $_REQUEST[$k] = $datum;
            $_POST[$k] = $datum;
        }

        static::$app->request = new Request();
        static::$app->router->request = static::$app->request;

        $data = static::$app->router->resolve();

        @(static::$app->response)();

        if (is_object($data) && !$data instanceof Response && method_exists($data, '__invoke')) {
            $data = $data();
        }
        elseif (is_object($data) && !$data instanceof Response && method_exists($data, '__toString')) {
            $data = $data . '';
        }
        elseif($data instanceof Response) {
            @$data();
            static::$app->response = $data;
            $data = $data->getContent();
        }

        self::$response_body = $data;
        $arr = ["response" => static::$app->response, "body" => $data];
        $this->exit();

        return $arr;
    }

    protected function exit()
    {
        $_GET = $this->_GET;
        $_POST = $this->_POST;
        $_REQUEST = $this->_REQUEST;
        $_SERVER = $this->_SERVER;
    }

    public function get($uri)
    {
        return $this->add(strtoupper(__FUNCTION__), $uri);
    }

    public function post($uri, $data = [])
    {
        return $this->add(strtoupper(__FUNCTION__), $uri, $data);
    }

    public function put($uri, $data = [])
    {
        return $this->add(strtoupper(__FUNCTION__), $uri, $data);
    }

    public function patch($uri, $data = [])
    {
        return $this->add(strtoupper(__FUNCTION__), $uri, $data);
    }

    public function delete($uri, $data = [])
    {
        return $this->add(strtoupper(__FUNCTION__), $uri, $data);
    }

    public function head($uri)
    {
        return $this->add(strtoupper(__FUNCTION__), $uri);
    }
}
