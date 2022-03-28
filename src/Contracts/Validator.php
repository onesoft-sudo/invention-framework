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

namespace OSN\Framework\Contracts;

use OSN\Framework\Exceptions\ValidatorException;

interface Validator
{
    public const RULE_REQUIRED = "required";
    public const RULE_EMAIL = "email";
    public const RULE_MIN = "min";
    public const RULE_MAX = "max";
    public const RULE_CONFIRMED = "confirmed";
    public const RULE_UNIQUE = "unique";
    public const RULE_NUMBER = "number";
    public const RULE_INT = "int";
    public const RULE_FLOAT = "float";
    public const RULE_IMAGE = "image";
    public const RULE_FILTER_SPECIAL_CHARS = "filterSpecialChars";
    public const RULE_FILTER_SPECIAL_CHARS_FULL = "filterSpecialCharsFull";

    /**
     * @param array|null $errorMessages
     * @return self
     */
    public function setErrorMessages(?array $errorMessages): self;

    /**
     * @param array $rules
     * @return self
     */
    public function setRules(array $rules): self;

    /**
     * Validate the data according to the rules.
     *
     * @return bool
     * @throws ValidatorException
     * @todo Add support for error logging
     */
    public function validate(): bool;

    public function addError(string $field, string $rule, string $wholeRule, array $ruleArguments = []);

    public function addErrorRaw(string $field, string $rule, string $message);

    /**
     * @throws ValidatorException
     */
    public function validatedRaw(): array;

    /**
     * @throws ValidatorException
     */
    public function validated(): array;

    public function sanitize();

    public function sanitized(): array;

    public function errors();
}