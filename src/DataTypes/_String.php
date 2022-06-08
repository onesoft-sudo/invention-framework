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

namespace OSN\Framework\DataTypes;

/**
 * A wrapper for strings, which provides a lot of useful methods
 * for working with strings.
 *
 * @package OSN\Framework\DataTypes
 * @author Ar Rakin <rakinar2@gmail.com>
 * @todo Add Docblock
 */
class _String implements DataTypeInterface
{
    private string $data;

    public function __construct(string $string = '')
    {
        $this->data = $string;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return $this->data;
    }

    /**
     * @param $value
     * @return void
     */
    public function set($value)
    {
        $this->data = (string) $value;
    }

    /*
     * The helper methods.
     */

    public function slug2camel(): static
    {
        return new static(str_replace(' ', '', lcfirst(ucwords(str_replace('-', ' ', $this->data)))));
    }

    public function slug2className(): static
    {
        return new static(str_replace(' ', '', ucwords(str_replace('-', ' ', $this->data))));
    }

    public function removeExtraQuotes()
    {
        $value = $this->data;

        if (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'")) {
            $value = substr($value, 1, -1);
        }

        return new static($value);
    }

    public static function from(string|\Stringable $str)
    {
        return new static($str);
    }

    public function len(bool $countWhiteSpaces = true): int
    {
        return $countWhiteSpaces ? strlen($this->data) : strlen(str_replace(' ', '', $this->data));
    }

    public function substr(int $from, int $to = -1)
    {
        if ($to === -1)
            $to = strlen($this->data);

        return substr($this->data, $from, $to);
    }

    public function ltrim(): string
    {
        return ltrim($this->data);
    }

    public function rtrim(): string
    {
        return rtrim($this->data);
    }

    public function trim(): string
    {
        return trim($this->data);
    }

    public function escape(): string
    {
        $str = $this->data;

        if (preg_match_all('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $this->data,$matches)) {
            if (!empty($matches[0])){
                $replacements = implode($matches[0]);
                $str = addcslashes($str, $replacements);
            }
        }

        return $str;
    }

    public function specialChars(): string
    {
        return htmlspecialchars($this->data);
    }

    public function specialCharsDecode(): string
    {
        return htmlspecialchars_decode($this->data);
    }

    public function entities(int $params = ENT_COMPAT, ?string $encoding = null, bool $double_encoding = true): string
    {
        return htmlentities($this->data, $params, $encoding, $double_encoding);
    }

    public function entitiesDecode(int $params = ENT_COMPAT, ?string $encoding = null): string
    {
        return html_entity_decode($this->data, $params, $encoding);
    }

    public function parseInt(): int
    {
        return (int) $this->data;
    }

    public function parseFloat(): int
    {
        return (float) $this->data;
    }

    public function parseDouble(): int
    {
        return (double) $this->data;
    }

    public function random(int $len = 16, string$chars = null): string
    {
        $chars = str_split($chars ?? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_');
        $random = '';

        for ($i = 0; $i < $len; $i++) {
            $rand = array_rand($chars, 1);
            $random .= $chars[$rand];
        }

        return $random;
    }

    public function toJSON()
    {
        return json_encode($this->data);
    }

    public function parseJSON()
    {
        return json_decode($this->data);
    }

    public function camel2slug()
    {
        return new static(strtolower(preg_replace('/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', '-', $this->data)));
    }

    public function camel2snake()
    {
        return new static(strtolower(preg_replace('/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', '_', $this->data)));
    }

    public function pascal2slug()
    {
        return $this->camel2slug();
    }

    public function slug(string $delim = '-')
    {
        $delims = "\\\n\r~`!@#\$%^&*()_+=\"\';:,[]{}?<>";
        return strtolower(preg_replace("/( )+/", $delim, trim(preg_replace("/[(\-+) \\(\/)\n\?\<\>\r\~\`\!\@\#\$\%\^\&\*\(\)\_\+\=\"\'\;\:\,\[\]\{\}]/", ' ', $this->data))));
    }

    public function test(string $regex): bool
    {
        return preg_match($regex, $this->data);
    }

    public function match(string $regex, &$matches = null, int $flags = 0, int $offset = 0)
    {
        return preg_match($regex, $this->data, $matches, $flags, $offset);
    }

    public function replace($regex, $replacement, int $limit = -1, &$count = null)
    {
        return preg_replace($regex, $replacement, $this->data, $limit,$count);
    }

    public function isURL()
    {
        return filter_var($this->data, FILTER_VALIDATE_URL);
    }

    public function strLastReplace($search, $replace)
    {
        $pos = strrpos($this->data, $search);

        if ($pos !== false) {
            return substr_replace($this->data, $replace, $pos, strlen($search));
        }

        return $this->data;
    }

    public function removeMultipleSpaces()
    {
        return preg_replace('/( +)/', ' ', $this->data);
    }
}
