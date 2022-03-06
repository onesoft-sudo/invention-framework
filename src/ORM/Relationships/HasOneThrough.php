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

namespace OSN\Framework\ORM\Relationships;


use OSN\Framework\Core\Model;
use OSN\Framework\Database\Query;

class HasOneThrough extends HasOne
{
    protected Model $bridge;

    public function __construct(Model $baseModel, Model $relationalModel, Model $bridge, bool $initParent = true)
    {
        $this->bridge = $bridge;
        parent::__construct($baseModel, $relationalModel, $initParent);
    }

    protected function makeQuery()
    {
        $subQuery = new Query();
        $data = $subQuery
            ->select($this->bridge->table, [$this->bridge->primaryColumn])
            ->where($this->bridge->table . '.' . $this->tableToForeignColumn($this->baseModel->table), $this->baseModel->get($this->baseModel->primaryColumn));

        $data2 = $data->get();

        if ($data2->count() < 1) {
            return $data;
        }

        $bridge_id = $data2[0][$this->bridge->primaryColumn];

        return $this->query
            ->select($this->relationalModel->table)
            ->where($this->relationalModel->table . '.' . $this->tableToForeignColumn($this->bridge->table), $bridge_id);
    }
}
