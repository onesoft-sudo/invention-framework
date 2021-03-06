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

namespace OSN\Framework\Security;


use App\Models\Post;
use App\Models\User;
use OSN\Framework\Core\Model;
use OSN\Framework\Foundation\Bootable;

abstract class Policy
{
    use Bootable;

    const ACTION_INDEX = 'index';
    const ACTION_VIEW = 'view';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    public function __construct(protected ?User $user, protected ?Model $model = null)
    {
        $this->bootUp();
    }

    public function can(string $action): bool
    {
        $args = [$this->user];

        if ($action !== static::ACTION_INDEX) {
            $args[] = $this->model;
        }

        return method_exists($this, $action) ? (call_user_func_array([$this, $action], $args) ?? false) : false;
    }
}
