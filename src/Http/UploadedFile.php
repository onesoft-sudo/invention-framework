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

/**
 * The uploaded file wrapper class.
 *
 * @package OSN\Framework\Http
 * @author Ar Rakin <rakinar2@gmail.com>
 */
class UploadedFile implements JsonSerializable, \Stringable
{
    /**
     * Image file mime types.
     *
     * @var string[]
     */
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

    /**
     * The raw data about the file, via $_FILES.
     *
     * @var array
     */
    protected array $raw;

    /**
     * The file name.
     *
     * @var string|mixed
     */
    public string $name;

    /**
     * File temporary name.
     *
     * @var string|mixed
     */
    public string $tmpName;

    /**
     * The error code. 0 means no error.
     *
     * @var int|mixed
     */
    public int $error;

    /**
     * Determine if there is an error.
     *
     * @var bool
     */
    public bool $hasError;

    /**
     * Full path of the file.
     *
     * @var string|mixed
     */
    public string $fullPath;

    /**
     * File mime type.
     *
     * @var string|mixed
     */
    public string $mimeType;

    /**
     * File size in bytes.
     *
     * @var int|mixed
     */
    public int $size;

    /**
     * Determine if the file is saved/
     *
     * @var bool
     */
    public bool $isSaved;

    /**
     * UploadedFile constructor.
     *
     * @param array $rawData
     */
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

    /**
     * Serialize the object.
     *
     * @return array
     */
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

    /**
     * Un-serialize the object.
     *
     * @param array $data
     */
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
     * Convert the object ot string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get the size in readable format (KB, MB).
     *
     * @return string
     */
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

    /**
     * Save the file.
     *
     * @todo Implement
     */
    public function save()
    {
        $this->isSaved = true;
    }

    /**
     * Check if the file is an image, via the mime type.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array($this->mimeType, static::$imageTypes);
    }
}