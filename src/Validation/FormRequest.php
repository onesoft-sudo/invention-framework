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

namespace OSN\Framework\Validation;


abstract class FormRequest
{
    protected Validator $validator;

    abstract public function rules(): array;
    abstract public function authorize(): bool;

    public function __construct(public array $data = [])
    {
        $this->validator = new Validator($data);
    }

    public function validate(): bool
    {
        $this->validator->setRules($this->rules());
        return $this->validator->validate();
    }

    public function validated(): array
    {
        return $this->validator->validated();
    }

    public function sanitized(): array
    {
        return $this->validator->sanitized();
    }

    public function __get(string $name)
    {
        return request()->$name;
    }

    public function all(): array
    {
        return request()->all();
    }

    public function getErrors()
    {
        return $this->validator->errors();
    }
}





















