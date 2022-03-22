<?php


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