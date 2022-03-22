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

use OSN\Framework\Exceptions\PropertyNotFoundException;
use OSN\Framework\Exceptions\ValidatorException;
use OSN\Framework\Http\RequestValidator;
use OSN\Framework\Utils\Arrayable;
use OSN\Framework\Validation\Validator;

/**
 * The HTTP request wrapper class.
 *
 * @package OSN\Framework\Http
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Request implements Arrayable
{
    use HTTPRequestParser;

    /**
     * The request method.
     *
     * @var string
     */
    public string $method;

    /**
     * The real request method.
     *
     * @var string
     */
    public string $realMethod;

    /**
     * The request URI.
     *
     * @var string
     */
    public string $uri;

    /**
     * The base request URi.
     *
     * @var string
     */
    public string $baseURI;

    /**
     * The request protocol.
     *
     * @var string
     */
    public string $protocol;

    /**
     * The server host (hostname + port).
     *
     * @var string
     */
    public string $host;

    /**
     * The server hostname.
     *
     * @var string
     */
    public string $hostname;

    /**
     * The server port.
     *
     * @var string
     */
    public string $port;

    /**
     * Determine if the connection uses SSL.
     *
     * @var bool
     */
    public bool $ssl;

    /**
     * The request query string.
     *
     * @var string
     */
    public string $queryString;

    /**
     * The client IP address.
     *
     * @var string
     */
    public string $ip;

    /**
     * $_POST data.
     *
     * @var object
     */
    public object $post;

    /**
     * $_GET data.
     *
     * @var object
     */
    public object $get;

    /**
     * Old data that was submitted via a "write" request method.
     *
     * @var array|null
     */
    protected ?array $old;

    /**
     * Uploaded files.
     *
     * @var array
     */
    public array $files;

    /**
     * Request headers.
     *
     * @var object
     */
    public object $headers;

    /**
     * Determines the error mode.
     *
     * @var bool
     */
    private bool $errmode_exception;

    /**
     * Determine if the system should auto-validate request data.
     *
     * @var bool
     */
    public bool $autoValidate = false;

    /**
     * The request validator.
     *
     * @var false|mixed|Validator
     */
    protected Validator $validator;

    /**
     * Request constructor.
     *
     * @param array|null $data
     * @param bool $errmode_exception
     */
    public function __construct(?array $data = null, bool $errmode_exception = true)
    {
        $this->update($data);
        $this->errmode_exception = $errmode_exception;

        if (!empty($this->all()))
            session()->set('__old_data', $this->all());
    }

    /**
     * Retrieve the old submitted data from session.
     *
     * @param null $key
     * @return array|mixed|null
     */
    public function old($key = null)
    {
        if (!isset($this->old) || $this->old == null)
            $this->old = session()->getFlash('__old_data');

        return $key === null ? $this->old : ($this->old[$key] ?? null);
    }

    /**
     * Get request data that was submitted using method GET.
     *
     * @param string $key
     * @return false
     */
    public function get(string $key)
    {
        return $this->get->$key ?? false;
    }

    /**
     * Get request data that was submitted using method POST.
     *
     * @param string $key
     * @return false
     */
    public function post(string $key)
    {
        return $this->post->$key ?? false;
    }

    /**
     * Retrieve an uploaded file.
     *
     * @param string $key
     * @return false|mixed
     */
    public function file(string $key)
    {
        return $this->files[$key] ?? false;
    }

    /**
     * Determine if the request method is type of "write".
     *
     * @return bool
     */
    public function isWriteRequest(): bool
    {
        if(in_array($this->method, ["POST", 'PUT', 'PATCH', "DELETE"])) {
            return true;
        }

        return false;
    }

    /**
     * Get a request data field.
     *
     * @throws PropertyNotFoundException
     */
    public function __get($name)
    {
        if (!$this->isWriteRequest()) {
            $prop = $this->get($name);
        }
        else {
            $prop = $this->post($name);
        }

        if ($prop === false) {
            $prop = $this->file($name);
        }

        if ($prop === false && $this->errmode_exception)
            throw new PropertyNotFoundException("The given field was not found");

        return $prop;
    }

    /**
     * Get a request header.
     *
     * @param $key
     * @return string|false
     */
    public function header($key)
    {
        return $this->headers->$key ?? false;
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function getMethod()
    {
        $realMethod = $this->realMethod;

        if ($realMethod !== 'POST' || !isset($this->post->__method))
            return $realMethod;

        return strtoupper($this->post->__method);
    }

    /**
     * Get specific request data fields.
     *
     * @param array $only
     * @return array
     */
    public function only(array $only): array
    {
        $arr = [];

        foreach ($only as $key) {
            $arr[$key] = $this->$key ?? null;
        }

        return $arr;
    }

    /**
     * Get all fields except the specified fields/
     *
     * @param array $except
     * @return array
     */
    public function except(array $except): array
    {
        $arr = array_merge((array) $this->get, (array) $this->post, $this->files);

        foreach ($except as $key) {
            if (isset($arr[$key]))
                unset($arr[$key]);
        }

        return $arr;
    }

    /**
     * Get all fields.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->except([]);
    }

    /** @todo */
    public function rules(): array
    {
        return [];
    }

    /** @todo */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Determine if the request data array has the given field.
     *
     * @param string $field
     * @return bool
     */
    public function has(string $field): bool
    {
        try {
            $tmp = $this->{$field};

            if ($tmp === null)
                return false;

            return true;
        }
        catch (PropertyNotFoundException $e) {
            return false;
        }
    }

    /**
     * An alias of $this->>has()
     *
     * @param string $field
     * @return bool
     */
    public function hasField(string $field): bool
    {
        return $this->has($field);
    }

    /**
     * Update the request data array.
     *
     * @param array|null $data
     */
    public function update(?array $data = null)
    {
        if ($data === null) {
            $data = [
                "get" => $_GET,
                "post" => $_POST,
                "files" => $_FILES,
                "method" => strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? '')),
                "uri" => $_SERVER['REQUEST_URI'] ?? '',
                "protocol" => $_SERVER['SERVER_PROTOCOL'] ?? '',
                "host" => $_SERVER["HTTP_HOST"] ?? '',
                "ssl" => isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on',
                "ip" => $_SERVER['REMOTE_ADDR'],
                "headers" => headers()
            ];
        }

        $data['get'] = array_map(function ($val) {
            return filter_var($val,FILTER_SANITIZE_SPECIAL_CHARS);
        }, $data['get']);

        $data['post'] = array_map(function ($val) {
            return filter_var($val,FILTER_SANITIZE_SPECIAL_CHARS);
        }, $data['post']);

        $this->post = (object) $data["post"];
        $this->get = (object) $data["get"];
        $this->files = array_map(fn($file) => $file['error'] === UPLOAD_ERR_NO_FILE ? null : new UploadedFile($file), $data["files"]);

        $this->realMethod = $data["method"];
        $this->method = $this->getMethod();
        $this->uri = $data["uri"];
        $this->baseURI = $this->getBaseURI($this->uri);
        $this->protocol = $data["protocol"];
        $this->host = $data["host"];
        $this->hostname = $this->getHost($this->host);
        $this->port = $this->getPort($this->host);
        $this->ssl = $data["ssl"];
        $this->queryString = $this->getQueryString($this->uri);
        $this->ip = $data["ip"];

        $this->headers = (object) $data["headers"];
    }

    /**
     * Return the array representation of the current object.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Validate the request data.
     *
     * @param array $rules
     * @return bool
     */
    public function validate(array $rules): bool
    {
        try {
            $this->validator = Validator::make($this, $rules);
            $this->validator->validate();
            return true;
        }
        catch (ValidatorException $e) {
            return false;
        }
    }

    /**
     * Validate the request data and get the validated data.
     *
     * @return array
     * @throws ValidatorException
     */
    public function validated(): array
    {
        return $this->validator?->validated();
    }

    /**
     * Validate the request data and get the sanitized data.
     *
     * @return array
     */
    public function sanitized(): array
    {
        return $this->validator?->sanitized();
    }

    public function url()
    {
        return $this->protocol . '://' . $this->host . $this->uri;
    }

    public function oldURL()
    {
        if ($this->header('Referer') !== false) {
            return $this->header('Referer');
        }

        return null;
    }
}
