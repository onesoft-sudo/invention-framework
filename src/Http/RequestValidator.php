<?php


namespace OSN\Framework\Http;


use OSN\Framework\Exceptions\HTTPException;
use OSN\Framework\Exceptions\PropertyNotFoundException;

trait RequestValidator
{
    public array $errors = [];
    protected bool $fixFieldNames = true;

    protected function addError($field, $rule, $errmsg)
    {
        $this->errors[$field][$rule] = $errmsg;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return empty($this->errors);
    }

    public function validate(array $customRules = null, bool $autoRedirect = false): bool
    {
        $rules = $customRules ?? $this->rules();

        foreach ($rules as $field => $ruleList) {
            try {
                $value = $this->{$field};
            }
            catch (PropertyNotFoundException $e) {
                $notSet = true;
            }

            if (isset($notSet))
                $value = null;

            $readableField = trim(str_replace('_', ' ', $field));

            foreach ($ruleList as $rule) {
                if ($rule === "int" && isset($this->{$field}) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, $rule, "The $readableField must be an integer");
                }

                if ($rule === "float" && isset($this->{$field}) && !filter_var($value, FILTER_VALIDATE_FLOAT)) {
                    $this->addError($field, $rule, "The $readableField must be a float");
                }

                if ($rule === "number" && isset($this->{$field}) && !($value === 0 || filter_var($value, FILTER_VALIDATE_FLOAT) || filter_var($value, FILTER_VALIDATE_INT))) {
                    $this->addError($field, $rule, "The $readableField must be a valid number");
                }

                if (preg_match("/max:\d+/", $rule)) {
                    $pos = strpos($rule, ":") + 1;
                    $maxValue = substr($rule, $pos);

                    if (strlen($value) > $maxValue) {
                        $this->addError($field, "max", "The maximum length of $readableField must be $maxValue");
                    }
                }

                if (preg_match("/min:\d+/", $rule)) {
                    $pos = strpos($rule, ":") + 1;
                    $minValue = substr($rule, $pos);

                    if (strlen($value) < $minValue) {
                        $this->addError($field, "min", "The minimum length of $readableField must be $minValue");
                    }
                }

                if ($rule === "email" && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, $rule, "The $readableField must be a valid email");
                }

                if ($rule === "required" && (isset($notSet) || trim($value) == '' || !isset($value))) {
                    $this->addError($field, $rule, "The $readableField is required");
                }

                if ($rule === "confirmed") {
                    $newField = $field . "_confirmation";

                    if (!$this->hasField($newField) || $value !== $this->$newField) {
                        $this->addError($newField, $rule, "The $readableField confirmation must be same as $readableField");
                    }
                }
            }
        }

        if (!empty($this->getErrors())) {
            session()->set('__validation_errors', $this->getErrors());

            if ($autoRedirect) {
                if (method_exists($this, 'handleInvalid')) {
                    $this->handleInvalid();
                }
                else {
                    if ($this->header('Referer')) {
                        header("HTTP/1.1 406 Not Acceptable");
                        header("Location: " . $this->header('Referer'));
                        exit();
                    }
                    else {
                        throw new HTTPException(406);
                    }
                }
            }

            return false;
        }

        return true;
    }
}
