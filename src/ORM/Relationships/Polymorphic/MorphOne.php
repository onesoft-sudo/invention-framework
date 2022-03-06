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

namespace OSN\Framework\ORM\Relationships\Polymorphic;


use OSN\Framework\ORM\PolymorphicRelationship;

class MorphOne extends PolymorphicRelationship
{
    /**
     * @return mixed
     */
    protected function makeQuery()
    {
        return $this->query
            ->select($this->relationalModel->table)
            ->where($this->relationalModel->table . '.' . $this->keyword . '_id', $this->baseModel->get($this->baseModel->primaryColumn))
            ->andWhere($this->relationalModel->table . '.' . $this->keyword . '_type', get_class($this->baseModel));
    }

    public function get()
    {
        $data = parent::get();
        return $data->hasGet(0);
    }
}
