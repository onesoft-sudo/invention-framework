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


use App\Models\User;
use OSN\Framework\Core\App;
use OSN\Framework\Core\Model;

class Auth
{
    protected ?User $user = null;

    public function __construct()
    {
//        $q = App::db()->prepare("SELECT * FROM users WHERE uid = :uid");
//        $q->execute(["uid" => App::session()->get("uid")]);
//        $userData = $q->fetchAll(\PDO::FETCH_ASSOC);
//
//        if (count($userData) > 0) {
//            $user = new User();
//
//            $user->uid = $userData[0]['uid'];
//            $user->name = $userData[0]['name'];
//            $user->email = $userData[0]['email'];
//            $user->username = $userData[0]['username'];
//            $user->password = hash('sha1', $userData[0]['password']);
//
//            $this->user = $user;
//        }

        $this->user = User::find(App::session()->get('uid'));
    }

    public function isAuthenticated(): bool
    {
        return App::session()->get("uid") !== null;
    }

    /**
     * @return bool
     */
    public function authUser(User $model)
    {
        $q = App::db()->prepare("SELECT * FROM users WHERE username = :u AND password = :p");

        $username = $model->username;
        $password = $model->password;

        $q->execute([
            "u" => $username,
            "p" => $password
        ]);

        $userData = $q->fetchAll(\PDO::FETCH_ASSOC);

        if (count($userData) > 0) {
            App::session()->set('uid', $userData[0]['uid']);
            App::session()->set('name', $userData[0]['name']);
            App::session()->set('email', $userData[0]['email']);
            App::session()->set('username', $userData[0]['username']);

            return true;
        }
        else {
            return false;
        }
    }

    public function user(): ?User
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        return $this->user;
    }

    public function destroyAuth()
    {
        App::session()->destroy();
    }

    public function can(Model|string $model, string $action): bool
    {
        if (is_string($model)) {
            $model = new $model();
        }

        return $model->can($action);
    }
}
