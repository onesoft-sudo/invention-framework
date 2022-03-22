<?php


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





















