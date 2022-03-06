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

namespace OSN\Framework\Cache;

class Cache
{
    protected string $cachedir;
    protected object $caches;

    /**
     * Cache constructor.
     * @param string $cachedir
     */
    public function __construct(string $cachedir)
    {
        $this->cachedir = $cachedir . '/app';

        if (!file_exists($this->cachedir))
            $this->mkdir('', 0775, true);

        if (!file_exists($this->files()))
            $this->mkdir('files', 0775, true);

        if (!file_exists($this->raw()))
            $this->mkdir('raw', 0775, true);

        $this->caches = json_decode(file_get_contents($this->cachedir . '/cacheconfig.json'));
        $this->purge();
    }

    protected function mkdir(string $dir, int $mode = 0755, bool $recursive = false): bool
    {
        return mkdir($this->cachedir . "/" . $dir, $mode, $recursive);
    }

    public function files(): string
    {
        return ($this->cachedir . '/files');
    }

    public function raw(): string
    {
        return ($this->cachedir . '/raw');
    }

    protected function addFile($file): string
    {
        $filename = "";

        if (is_resource($file)) {
            $metaData = stream_get_meta_data($file);
            $filename = $metaData["uri"];
        }
        else {
            $filename = $file;
        }

        $newpath = $this->transform(date('Y-m-d_H-i-s_') . rand());
        copy($filename, $newpath);

        return $newpath;
    }

    public function findById($id)
    {
        foreach ($this->caches as $id1 => $cache) {
            if ($id1 == $id) {
                $cache->id = $id;
                return $cache;
            }
        }

        return null;
    }

    public function add(array $data)
    {
        $this->addOnly($data);
        $this->update();
    }

    protected function addOnly(array $data)
    {
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['expires_at'] = $data['expires_at'] . '';
        $original = $data;
        unset($data['id']);
        $this->caches->{$original['id']} = (object) $data;
    }

    protected function update()
    {
        file_put_contents($this->cachedir . '/cacheconfig.json', json_encode($this->caches));
    }

    protected function transform(string $file): string
    {
        return $this->cachedir . "/" . $file;
    }

    public function purge()
    {
        $newObject = (object) [];

        foreach ($this->caches as $id => $cache) {
            if (now() >= $cache->expires_at) {
                unlink((isset($cache->real) ? $this->files() : $this->raw()) . "/" . $id);
                continue;
            }

            $newObject->$id = $cache;
        }

        $this->caches = $newObject;
        $this->update();
    }

    public function put($id, $value, $lifetime = null, bool $file = false): bool
    {
        if (!$file) {
            $this->add([
                "id" => $id,
                "expires_at" => $lifetime ?? "true"
            ]);

            file_put_contents($this->raw() . "/" . $id, $value);
        }
        else {
            $this->add([
                "id" => $id,
                "real" => $value,
                "expires_at" => $lifetime ?? "true"
            ]);

            copy($value,$this->files() . "/" . $id);
        }

        return true;
    }

    public function putFile($id, $path, $lifetime = null): bool
    {
        return $this->put($id, $path, $lifetime, true);
    }

    public function remove($id)
    {
        if (isset($this->caches->$id))
            $this->caches->$id->expires_at = now();

        $this->purge();
    }

    public function removeAll()
    {
        foreach ($this->caches as $key => $cache) {
            $this->caches->$key->expires_at = now();
        }

        $this->purge();
    }

    public function removeInternals(): void
    {
        $ignore = ['.', '..', '.gitignore'];

        foreach ([basepath('/var/cache/powerparser')] as $path) {
            $files = scandir($path);

            foreach ($files as $file) {
                if (!in_array($file, $ignore)) {
                    unlink($path . '/' . $file);
                }
            }
        }
    }

    public function getFile($id): string
    {
        return (isset($this->caches->$id->real) ? $this->files() : $this->raw()) . "/" . $id;
    }

    public function has($id): bool
    {
        return is_file($this->getFile($id));
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new \RuntimeException("Couldn't find cache with the given ID: $id");
        }

        return file_get_contents($this->getFile($id));
    }
}
