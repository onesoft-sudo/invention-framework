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

namespace OSN\Framework\Database\MySQL;

use \OSN\Framework\Database\Common\Column as CommonColumn;

/**
 * Column class for using with MySQL.
 *
 * @package OSN\Framework\Database\MySQL
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class Column extends CommonColumn
{
    /**
     * Append AUTO_INCREMENT
     *
     * @return $this
     */
    public function autoIncrement(): self
    {
        return $this->append(" AUTO_INCREMENT", false);
    }
}