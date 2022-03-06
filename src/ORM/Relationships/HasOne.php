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


use Closure;
use OSN\Framework\Core\Database;
use OSN\Framework\Core\Model;
use OSN\Framework\Database\Query;
use OSN\Framework\ORM\DualRelationship;
use OSN\Framework\ORM\Relationship;

class HasOne extends DualRelationship
{
    protected function makeQuery()
    {
        return $this->query
            ->select($this->relationalModel->table)
            ->where($this->relationalModel->table . '.' . $this->tableToForeignColumn($this->baseModel->table), $this->baseModel->get($this->baseModel->primaryColumn));
    }

    public function last()
    {
        $this->query->orderBy($this->relationalModel->table . '.' . $this->relationalModel->primaryColumn, true);
        return $this;
    }

    public function first()
    {
        $this->query->orderBy($this->relationalModel->table . '.' . $this->relationalModel->primaryColumn, false);
        return $this;
    }

    public function get()
    {
        $data = parent::get();
        return $data->hasGet(0);
    }
}
