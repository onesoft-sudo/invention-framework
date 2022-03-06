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


use App\Http\Controllers\APIController;
use OSN\Framework\Core\Model;
use OSN\Framework\ORM\Relationships\BelongsTo;
use OSN\Framework\ORM\Relationships\BelongsToMany;
use OSN\Framework\ORM\Relationships\HasMany;
use OSN\Framework\ORM\Relationships\HasManyThrough;
use OSN\Framework\ORM\Relationships\HasOne;
use OSN\Framework\ORM\Relationships\HasOneThrough;
use OSN\Framework\ORM\Relationships\Polymorphic\MorphMany;
use OSN\Framework\ORM\Relationships\Polymorphic\MorphOne;
use OSN\Framework\ORM\Relationships\Polymorphic\MorphTo;

trait ORMBaseTrait
{
    public function hasMany(string $class): HasMany
    {
        /** @var Model $this */
        return new HasMany($this, new $class());
    }

    public function hasOne(string $class): HasOne
    {
        /** @var Model $this */
        return new HasOne($this, new $class());
    }

    public function hasOneThrough(string $relating, string $bridge): HasOneThrough
    {
        /** @var Model $this */
        return new HasOneThrough($this, new $relating(), new $bridge());
    }

    public function hasManyThrough(string $relating, string $bridge): HasManyThrough
    {
        /** @var Model $this */
        return new HasManyThrough($this, new $relating(), new $bridge());
    }

    public function belongsTo(string $class): BelongsTo
    {
        /** @var Model $this */
        return new BelongsTo($this, new $class());
    }

    public function belongsToMany(string $class, string $helper_table = ''): BelongsToMany
    {
        /** @var Model $this */
        return new BelongsToMany($this, new $class(), $helper_table);
    }

    public function morphOne(string $class, string $keyword = ''): MorphOne
    {
        /** @var Model $this */
        return new MorphOne($this, new $class(), $keyword);
    }

    public function morphTo(string $keyword = ''): MorphTo
    {
        /** @var Model $this */
        return new MorphTo($this, $keyword);
    }

    public function morphMany(string $class, string $keyword = ''): MorphMany
    {
        /** @var Model $this */
        return new MorphMany($this, new $class(), $keyword);
    }
}
