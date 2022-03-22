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

use Throwable;

/**
 * Class PropertyNotFoundException
 *
 * @package OSN\Framework\Exceptions
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class PropertyNotFoundException extends \Exception
{
    protected $code = 2;
    protected $message = 'The specified property was not found';
    protected $property;

    public function __construct($message = "", $property = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if ($property != null) {
            $this->property = $property;
            $this->message .= " '$property'";
        }
    }
}
