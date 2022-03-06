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


use OSN\Framework\Core\Collection;

trait ManyToManyTrait
{
    public function get()
    {
        /** @var Collection $data */
        /** @var Collection $data2 */
        $data = parent::baseGet();

        $data2 = collection();
        $class = get_class($this->relationalModel);

        $data->each(function ($value, $key) use ($class, $data2) {
            $model = new $class;

            $key1 = $this->tableToForeignColumn($this->relationalModel->table);
            $key2 = $this->tableToForeignColumn($this->baseModel->table);
            $model->pivot = new Pivot($key1, $key2);

            foreach ($value as $k => $datum) {
                if ($k === $key1 || $k === $key2) {
                    $model->pivot->$k = $datum;
                    continue;
                }

                $model->{$k} = $datum;
            }

            $data2->set($key, $model);
        });

        return $data2;
    }
}
