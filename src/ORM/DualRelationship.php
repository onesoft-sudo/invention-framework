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
use OSN\Framework\Core\Model;

abstract class DualRelationship extends Relationship
{
    protected ?Model $relationalModel = null;
    protected Model $baseModel;
    protected string $relationalModelClass;
    protected string $baseModelClass;

    public function __construct(Model $baseModel, ?Model $relationalModel, bool $initParent = true)
    {
        $this->baseModel = $baseModel;
        $this->relationalModel = $relationalModel;
        $this->baseModelClass = get_class($baseModel);

        if ($relationalModel !== null)
            $this->relationalModelClass = get_class($relationalModel);

        if ($initParent)
            parent::__construct();
    }

    public function baseGet()
    {
        return parent::get();
    }

    public function get()
    {
        /** @var Collection $data */
        /** @var Collection $data2 */
        $data = parent::get();

        $data2 = collection();
        $class = get_class($this->relationalModel);

        $data->each(function ($value, $key) use ($class, $data2) {
            $model = new $class;

            foreach ($value as $k => $datum) {
                $model->{$k} = $datum;
            }

            $data2->set($key, $model);
        });

        return $data2;
    }
}
