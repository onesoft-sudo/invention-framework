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


/**
 * Trait ResponseTrait
 *
 * @package OSN\Framework\Http
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait ResponseTrait
{
    /**
     * The response body.
     *
     * @var string|null
     */
    protected ?string $response;

    /**
     * Response status code.
     *
     * @var int
     */
    protected $code;

    /**
     * The response headers.
     *
     * @var array|null
     */
    public ?array $headers = [];

    /**
     * The response status text.
     *
     * @var string
     */
    protected string $statusText;

    /**
     * An array of all valid HTTP status codes.
     *
     * @var array|string[]
     */
    protected static array $responseCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

        200 => 'OK',
        201 => 'Created',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        300 => 'Multiple Choice',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a Teapot',
        419 => 'Page Expired',
        421 => 'Misdirected Request',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * Get response body.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->response === null ? '' : $this->response;
    }

    /**
     * Set the response content.
     *
     * @param string|null $response
     */
    public function setContent(?string $response): void
    {
        $this->response = $response;
    }

    /**
     * Get the status text.
     *
     * @return string
     */
    public function getStatusText(): string
    {
        return $this->statusText;
    }

    /**
     * Set the status text.
     *
     * @param string $statusText
     */
    public function setStatusText(string $statusText): void
    {
        $this->statusText = $statusText;
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Set the status code.
     *
     * @param int $code
     */
    public function setCode(int $code = 200)
    {
        $this->code = $code;
        $this->setStatusFromCode($code);
    }

    /**
     * Get the appropriate status from the given code.
     *
     * @param int $code
     * @return mixed|string
     */
    public static function getStatusFromCode(int $code)
    {
        return static::$responseCodes[$code] ?? 'Unknown Status Code';
    }

    /**
     * Set status text from the given code.
     *
     * @param int $code
     */
    public function setStatusFromCode(int $code)
    {
        $this->setStatusText(static::getStatusFromCode($code));
    }

    /**
     * Set response headers.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Set headers by merging the defaults and custom headers.
     *
     * @param array $custom_headers
     */
    public function setHeadersParsed(array $custom_headers = []): void
    {
        $headers = headers_list();

        $headers_array = $this->headers;

        foreach ($headers as $header) {
            $tmp = explode(':', $header);
            $key = trim($tmp[0]);
            array_shift($tmp);
            $headers_array[$key] = trim(implode(':', $tmp));
        }

        $headers_array = array_merge($headers_array, $custom_headers);

        $this->headers = $headers_array;
    }

    /**
     * Getter and setter for headers.
     *
     * @param $key
     * @param null $value
     * @return mixed|null
     */
    public function header($key, $value = null)
    {
        if ($value === null) {
            return $this->headers[$key] ?? null;
        }

        $this->headers[$key] = $value;
    }
}
