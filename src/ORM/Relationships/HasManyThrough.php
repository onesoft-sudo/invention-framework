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
use OSN\Framework\ORM\ManyToManyTrait;

class HasManyThrough extends HasMany
{
    protected Model $bridge;

    public function __construct(Model $baseModel /* project */, Model $relationalModel /* deployments */, Model $bridge, bool $initParent = true)
    {
        $this->bridge = $bridge;
        parent::__construct($baseModel, $relationalModel, $initParent);
    }

    protected function makeQuery()
    {
        $subQuery = new Query();

        $query = $subQuery
            ->select($this->bridge->table, [$this->bridge->primaryColumn])
            ->where($this->bridge->table . '.' . $this->tableToForeignColumn($this->baseModel->table), $this->baseModel->get($this->baseModel->primaryColumn));

        $data = $query->get();

        if ($data->count() < 1) {
            return $query;
        }

        $bridge_id = $data[0][$this->bridge->primaryColumn];

        return $this->query
            ->select($this->relationalModel->table)
            ->where($this->relationalModel->table . '.' . $this->tableToForeignColumn($this->bridge->table), $bridge_id);
    }
}
