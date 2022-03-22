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

namespace OSN\Framework\Console;

/**
 * Class Command
 *
 * @package OSN\Framework\Console
 * @author Ar Rakin <rakinar2@gmail.com>
 * @experimental
 */
class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * The base name of the command.
     *
     * @var string
     */
    protected string $basename;

    /**
     * The regex pattern of the command. This pattern is used to match and get arguments.
     *
     * @var string
     */
    protected string $regex;

    /**
     * The arguments passed to the command.
     *
     * @var string
     */
    protected string $params;

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description;

    /**
     * The help text of the command.
     *
     * @var string
     */
    protected string $helpText;

    /**
     * Determines if the command needs to be hidden from the list.
     *
     * @var bool
     */
    protected bool $hidden = false;
}
