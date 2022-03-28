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
use OSN\Framework\Utils\Arrayable;

class Validator implements \OSN\Framework\Contracts\Validator
{
    use Rules;
    use RuleErrorMessages;
    use Sanitizers;

    protected array $errors = [];
    protected ?array $errorMessages = [];

    public function __construct(array|object $data, protected array $rules = [])
    {
        $data = $data instanceof Arrayable ? $data->toArray() : $data;
        $this->data = (array) $data;
    }

    /**
     * @param array|null $errorMessages
     * @return Validator
     */
    public function setErrorMessages(?array $errorMessages): self
    {
        $this->errorMessages = $errorMessages;
        return $this;
    }

    /**
     * @param array $rules
     * @return Validator
     */
    public function setRules(array $rules): Validator
    {
        $this->rules = $rules;
        return $this;
    }

    public static function make(array|object $data, array $rules): static
    {
        return new static($data, $rules);
    }

    /**
     * Validate the data according to the rules.
     *
     * @throws ValidatorException
     * @return bool
     * @todo Add support for error logging
     */
    public function validate(): bool
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
                        $this->addError($field, $ruleExploded[0], $rule, $ruleArguments);
                      // $this->errors[$field][$ruleExploded[0]] = method_exists($this, $ruleErrorMethod) ? call_user_func_array([$this, $ruleErrorMethod], [$this->data[$field] ?? null, $field, ...$ruleArguments]) : "There was a validation error with this field";
                    }
                }
                else {
                    throw new ValidatorException("Invalid validation rule: {$rule}", 2);
                }
            }
        }

        return empty($this->errors);
    }

    public function addError(string $field, string $rule, string $wholeRule, array $ruleArguments = [])
    {
        $ruleArguments = [$this->data[$field] ?? null, $field, ...$ruleArguments];

        if ($this->errorMessages !== null) {
            if (isset($this->errorMessages[$field][$wholeRule])) {
                $this->addErrorRaw($field, $rule, $this->replaceErrorMessagePlaceholders($this->errorMessages[$field][$wholeRule], $ruleArguments));
                return;
            }

            if (isset($this->errorMessages[$field][$rule])) {
                $this->addErrorRaw($field, $rule, $this->replaceErrorMessagePlaceholders($this->errorMessages[$field][$rule], $ruleArguments));
                return;
            }
        }

        $ruleErrorMethod = "rule" . ucfirst($rule) . "Error";
        $this->addErrorRaw($field, $rule, method_exists($this, $ruleErrorMethod) ? call_user_func_array([$this, $ruleErrorMethod], $ruleArguments) : "There was a validation error with this field");
    }

    protected function replaceErrorMessagePlaceholders(string $msg, array $args): string
    {
        foreach ($args as $key => $value) {
            $msg = str_replace("{{$key}}", $value, $msg);
        }

        return $msg;
    }

    public function addErrorRaw(string $field, string $rule, string $message)
    {
        $this->errors[$field][$rule] = $message;
    }

    /**
     * @throws ValidatorException
     */
    public function validatedRaw(): array
    {
        $this->validate();
        return $this->data;
    }
    
    /**
     * @throws ValidatorException
     */
    public function validated(): array
    {
        $this->validate();
        return $this->sanitized();
    }

    public function sanitize()
    {
        $this->sanitized = $this->data;

        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                $ruleExploded = explode(':', $rule);
                $ruleArguments = $ruleExploded[1] ?? [];

                if (!empty($ruleArguments)) {
                    $ruleArguments = explode(',', $ruleArguments);
                }

                $ruleMethod = "sanitize" . ucfirst($ruleExploded[0]);

                if (method_exists($this, $ruleMethod)) {
                    $this->sanitized[$field] = call_user_func_array([$this, $ruleMethod], [$this->data[$field] ?? null, $field, ...$ruleArguments]);
                }
            }
        }
    }

    public function sanitized(): array
    {
        if (empty($this->sanitized))
            $this->sanitize();

        return $this->sanitized;
    }

    public function errors()
    {
        return $this->errors;
    }
}
