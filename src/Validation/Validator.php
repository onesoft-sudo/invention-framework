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


use Closure;
use OSN\Framework\Exceptions\ValidatorException;
use OSN\Framework\Http\RequestValidator;

class Validator
{
    use Rules;

    public function __construct(array|object $data, protected array $rules)
    {
        $this->data = (array) $data;
    }

    #[\Pure]
    public static function make(array|object $data, array $rules): static
    {
        return new static($data, $rules);
    }

    /**
     * Validate the data according to the rules.
     *
     * @throws ValidatorException
     * @return void
     */
    public function validate(): void
    {
        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                $ruleExploded = explode(':', $rule);
                $ruleArguments = $ruleExploded[1] ?? [];

                if (!empty($ruleArguments)) {
                    $ruleArguments = explode(',', $ruleArguments);
                }

                $ruleMethod = "rule" . ucfirst($ruleExploded[0]);

                if (method_exists($this, $ruleMethod)) {
                    if (!call_user_func_array([$this, $ruleMethod], [$this->data[$field] ?? null, $field, ...$ruleArguments])) {
                        throw new ValidatorException("Validation failed for field '{$field}' (rule '{$rule}', value '" . ($this->data[$field] ?? '') . "')", 1);
                    }
                }
                else {
                    throw new ValidatorException("Invalid validation rule: {$rule}", 2);
                }
            }
        }
    }
}
