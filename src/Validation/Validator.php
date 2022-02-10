<?php


namespace OSN\Framework\Validation;


use OSN\Framework\Http\RequestValidator;

class Validator
{
    use RequestValidator {
        RequestValidator::validate as protected _validate;
    }

    protected array $rules = [];

    public function rules()
    {
        return $this->rules;
    }

    public function authorize()
    {
        return true;
    }
}
