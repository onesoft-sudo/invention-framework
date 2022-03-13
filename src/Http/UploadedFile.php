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

namespace OSN\Framework\Http;


use JsonSerializable;

class UploadedFile implements JsonSerializable, \Stringable
{
    protected static $imageTypes = [
        "image/png",
        "image/gif",
        "image/cis-cod",
        "image/bmp",
        "image/pipeg",
        "image/jpeg",
        "image/pipeg",
        "image/svg+xml",
        "image/x-cmx",
        "image/x-icon",
        "image/x-cmu-raster",
        "image/x-portable-anymap",
        "image/x-portable-bitmap",
        "image/x-portable-graymap",
        "image/x-portable-pixmap",
        "image/x-rgb",
        "image/x-xbitmap",
        "image/x-xpixmap",
        "image/x-xwindowdump",
    ];

    protected array $raw;
    public string $name;
    public string $tmpName;
    public int $error;
    public bool $hasError;
    public string $fullPath;
    public string $mimeType;
    public int $size;
    public bool $isSaved;

    public function __construct(array $rawData)
    {
        $this->raw = $rawData;
        $this->name = $rawData['name'];
        $this->tmpName = $rawData['tmp_name'];
        $this->error = $rawData['error'] ?? 0;
        $this->hasError = $this->error !== 0;
        $this->fullPath = $rawData['full_path'];
        $this->mimeType = $rawData['type'];
        $this->size = $rawData['size'];
        $this->isSaved = false;
    }

    public function __serialize(): array
    {
        return [
            "name" => $this->name,
            "tmp_name" => $this->tmpName,
            "full_path" => $this->fullPath,
            "type" => $this->mimeType,
            "size" => $this->size,
            "size_readable" => $this->getSizeReadable(),
            "saved" => $this->isSaved
        ];
    }

    public function __unserialize(array $data): void
    {
        static::__construct($data);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->__serialize();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    public function getSizeReadable(): string
    {
        $size = $this->size;
        $unit = "B";

        if ($this->size >= (1024 ** 4)) {
            $size /= 1024 ** 4;
            $unit = "TB";
        }
        elseif ($this->size >= (1024 ** 3)) {
            $size /= 1024 ** 3;
            $unit = "GB";
        }
        elseif ($this->size >= (1024 ** 2)) {
            $size /= 1024 ** 2;
            $unit = "MB";
        }
        elseif ($this->size >= (1024)) {
            $size /= 1024;
            $unit = "KB";
        }

        return number_format($size, 2) . $unit;
    }

    public function save()
    {
        $this->isSaved = true;
    }

    public function isImage(): bool
    {
        return in_array($this->mimeType, static::$imageTypes);
    }
}