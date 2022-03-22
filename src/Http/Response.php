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
 * The HTTP response.
 *
 * @package OSN\Framework\Http
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Response
{
    use ResponseTrait;

    /**
     * The HTTP version.
     *
     * @var string
     */
    protected string $httpVersion = '1.1';

    /**
     * Response constructor.
     *
     * @param string|null $response
     * @param int $code
     * @param array $headers
     */
    public function __construct(?string $response = null, int $code = 200, array $headers = [])
    {
        $this->setContent($response);
        $this->setCode($code);
        $this->setStatusFromCode($code);
        $this->setHeadersParsed($headers);
    }

    /**
     * Set the response data.
     *
     * @return void
     */
    protected function setData()
    {
        @header("HTTP/{$this->httpVersion} {$this->getCode()} {$this->getStatusText()}");
        foreach ($this->headers as $header => $value) {
            @header("$header: $value");
        }
    }

    /**
     * When attempted to convert to a string, set the response data and return
     * the response body.
     *
     * @return string
     * @throws \OSN\Framework\Exceptions\EventException
     */
    public function __toString()
    {
        $this->setData();

        if ($this->getCode() > 299 && $this->getCode() < 400 && server('APP_ENV') != 'testing') {
            app()->dispatch('AppRunningComplete', [app()]);
            exit();
        }

        return $this->getContent();
    }

    /**
     * When attempted to call the object, set the response data and return
     * the response body.
     *
     * @return string
     * @throws \OSN\Framework\Exceptions\EventException
     */
    public function __invoke(): string
    {
        $this->setData();

        if ($this->getCode() > 299 && $this->getCode() < 400 && server('APP_ENV') != 'testing') {
            app()->dispatch('AppRunningComplete', [app()]);
            exit();
        }

        return $this->getContent();
    }

    /**
     * Send a redirect response.
     *
     * @param $url
     * @param int $code
     * @return $this
     */
    public function redirect($url, int $code = 302): self
    {
        $this->setCode($code);
        $this->header('Location', $url);
        return $this;
    }

    /**
     * Redirect the user immediately and stop code execution.
     *
     * @param $url
     * @param int $code
     * @throws \OSN\Framework\Exceptions\EventException
     */
    public function redirectImmediately($url, $code = 302)
    {
        $text = $this->getStatusFromCode($code);
        header("HTTP/{$this->httpVersion} $code $text");
        header("Location: $url");
        app()->dispatch('AppRunningComplete', [app()]);
        exit();
    }
}
