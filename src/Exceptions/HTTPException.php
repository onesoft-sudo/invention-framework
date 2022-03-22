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

namespace OSN\Framework\Exceptions;

use Exception;
use OSN\Framework\Http\ResponseTrait;
use OSN\Framework\Http\Status;
use Throwable;

/**
 * Class HTTPException
 *
 * @package OSN\Framework\Exceptions
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class HTTPException extends Exception
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $code = 500;

    /**
     * The headers that needs to be sent while this exception is handled.
     *
     * @var array
     */
    protected array $headers;

    /**
     * HTTPException constructor.
     *
     * @param int $code
     * @param string $message
     * @param array $headers
     * @param Throwable|null $previous
     */
    public function __construct($code = 500, $message = 'Internal Server Error', array $headers = [], Throwable $previous = null)
    {
        $status = new Status($code);
        $message = $status->getStatusFromCode($code);
        parent::__construct($message, $code, $previous);
        $this->headers = $headers;
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
