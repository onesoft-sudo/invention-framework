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


trait RuleErrorMessages
{
    public function ruleRequiredError(): string
    {
        return "This field is required";
    }

    public function ruleMinError($value, $field, $min): string
    {
        return "The length of this field must not be less than $min";
    }

    public function ruleMaxError($value, $field, $max): string
    {
        return "The length of this field must be less than $max";
    }
}