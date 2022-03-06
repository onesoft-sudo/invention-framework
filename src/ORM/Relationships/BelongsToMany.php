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


use OSN\Framework\Core\Collection;
use OSN\Framework\Core\Model;
use OSN\Framework\Database\Query;
use OSN\Framework\ORM\DualRelationship;
use OSN\Framework\ORM\ManyToManyTrait;
use OSN\Framework\ORM\Pivot;

class BelongsToMany extends DualRelationship
{
    use ManyToManyTrait;

    protected string $helper_table;

    /**
     * BelongsToMany constructor.
     * @param Model $baseModel
     * @param Model $relationalModel
     * @param string $helper_table
     */
    public function __construct(Model $baseModel, Model $relationalModel, string $helper_table = '')
    {
        $this->query = new Query();
        parent::__construct($baseModel, $relationalModel, false);

        if ($helper_table == '') {
            $helper_table = preg_replace('/s$/', '', $this->baseModel->table) . '_' . preg_replace('/s$/', '', $this->relationalModel->table);
        }

        $this->helper_table = $helper_table;
        $this->makeQuery();
    }

    protected function makeQuery()
    {
       return $this->query
            ->select($this->relationalModel->table)
            ->leftJoin($this->helper_table, $this->relationalModel->primaryColumn, $this->tableToForeignColumn($this->relationalModel->table))
            ->where($this->helper_table . '.' . $this->tableToForeignColumn($this->baseModel->table), $this->baseModel->get($this->baseModel->primaryColumn));
    }
}
