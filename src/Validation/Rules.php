<?php


namespace OSN\Framework\Validation;

/**
 * Trait Rules
 *
 * @package OSN\Framework\Validation
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait Rules
{
    protected array $data;

    /**
     * Validate if the given field exists and not empty.
     *
     * @param $data
     * @return bool
     */
    protected function ruleRequired($data): bool
    {
        return $data !== null && $data !== '';
    }

    /**
     * Validate an email address.
     *
     * @param $data
     * @return bool
     */
    protected function ruleEmail($data): bool
    {
        return (bool) filter_var($data, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate a number.
     *
     * @param $data
     * @return bool
     */
    protected function ruleNumber($data): bool
    {
        return $data === 0 || $data === '0' || filter_var($data, FILTER_VALIDATE_INT) || filter_var($data, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Validate an integer.
     *
     * @param $data
     * @return bool
     */
    protected function ruleInt($data): bool
    {
        return $data === 0 || $data === '0' || filter_var($data, FILTER_VALIDATE_INT);
    }

    /**
     * Validate a float.
     *
     * @param $data
     * @return bool
     */
    #[\Pure]
    protected function ruleFloat($data): bool
    {
        return !$this->ruleInt($data) && filter_var($data, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Validate that a numeric value is larger than the given value.
     *
     * @param $data
     * @param $field
     * @param int|float $min
     * @return bool
     */
    #[\Pure]
    protected function ruleMin($data, $field, int|float $min): bool
    {
        return $this->ruleNumber($data) && $data > $min;
    }

    /**
     * Validate that a numeric value is less than the given value.
     *
     * @param $data
     * @param $field
     * @param int|float $max
     * @return bool
     */
    #[\Pure]
    protected function ruleMax($data, $field, int|float $max): bool
    {
        return $this->ruleNumber($data) && $data < $max;
    }

    /**
     * Validate that the field is confirmed correctly with another field value.
     *
     * @param $data
     * @param string $field
     * @param string|null $confirmationField
     * @return bool
     */
    #[\Pure]
    protected function ruleConfirmed($data, string $field, ?string $confirmationField = null): bool
    {
        $c = $confirmationField ?? ($field . '_confirmation');
        return $this->ruleRequired($this->data[$c] ?? '') && $this->data[$c] === $data;
    }
}