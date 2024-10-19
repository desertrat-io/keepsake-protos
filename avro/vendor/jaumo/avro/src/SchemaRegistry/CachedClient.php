<?php

/**
 * Copyright 2024 Joyride GmbH.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Avro\SchemaRegistry;

use Doctrine\Common\Cache\Cache;

final class CachedClient implements AsyncClient
{
    private Cache $cache;
    private AsyncClient $decorated;

    public function __construct(
        Cache $cache,
        AsyncClient $decorated
    ) {
        $this->cache = $cache;
        $this->decorated = $decorated;
    }

    public function getRegisteredSchemaId(string $subject, string $schema): ?int
    {
        $key = self::getCacheKey($schema);
        if (false === $id = $this->cache->fetch($key)) {
            $id = $this->decorated->getRegisteredSchemaId($subject, $schema);

            if ($id !== null && !$this->cache->save($key, $id)) {
                throw CacheException::persistenceFailed($key, $id);
            }
        }

        return $id;
    }

    public function registerSchema(string $subject, string $schema): int
    {
        $key = self::getCacheKey($schema);
        $id = $this->decorated->registerSchema($subject, $schema);
        if (!$this->cache->save($key, $id)) {
            throw CacheException::persistenceFailed($key, $id);
        }

        return $id;
    }

    public function getSchema(int $id): string
    {
        $key = (string) $id;
        if (false === $schema = $this->cache->fetch($key)) {
            $schema = $this->decorated->getSchema($id);
            if (!$this->cache->save($key, $schema)) {
                throw CacheException::persistenceFailed($key, $schema);
            }
        }

        return $schema;
    }

    private static function getCacheKey(string $data): string
    {
        return \hash('fnv1a64', $data);
    }
}
