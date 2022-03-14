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

use OSN\Framework\Http\UploadedFile;

/**
 * Trait Rules
 *
 * @package OSN\Framework\Validation
 * @author Ar Rakin <rakinar2@gmail.com>
 */
trait Rules
{
    protected array $data = [];

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

    /**
     * Validate that the field value is an image file.
     *
     * @param $data
     * @return bool
     */
    protected function ruleImage($data): bool
    {
        return $data instanceof UploadedFile && $data->isImage();
    }

    /**
     * Validate that the field value is unique in the database.
     *
     * @param $data
     * @param string $field
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function ruleUnique($data, string $field, string $table, string $column): bool
    {
        $statement = db()->prepare("SELECT $column FROM $table WHERE $column = ?");
        $statement->execute([$data]);

        return count($statement->fetchAll()) < 1;
    }

    protected function ruleFilterSpecialChars(): bool
    {
        return true;
    }

    protected function ruleFilterSpecialCharsFull(): bool
    {
        return true;
    }
}