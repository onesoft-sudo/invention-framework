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

namespace OSN\Framework\ORM;


use OSN\Framework\Core\Model;

abstract class PolymorphicRelationship extends DualRelationship
{
    protected string $keyword;

    public function __construct(Model $baseModel, ?Model $relationalModel, string $keyword = "", bool $initParent = true)
    {
        if ($keyword === '' && $relationalModel !== null) {
            $keyword = preg_replace('/s$/', '', $relationalModel->table) . 'able';
        }

        $this->keyword = $keyword;
        parent::__construct($baseModel, $relationalModel, $initParent);
    }
}
