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

namespace Avro\Serialization;

use Avro\Model\Schema\Named;
use Avro\Model\Schema\NamespacedName;

final class SchemaRepository
{
    private array $schemas = [];

    public function add(Named $schema): void
    {
        $id = $schema->getFullName();

        if (\array_key_exists($id, $this->schemas)) {
            throw new \InvalidArgumentException(\sprintf('Name "%s" is already in use', $id));
        }

        $this->schemas[$id] = $schema;
    }

    public function resolve(NamespacedName $name): Named
    {
        return $this->resolveByString($name->getFullName());
    }

    public function resolveByString(string $id): Named
    {
        if (!\array_key_exists($id, $this->schemas)) {
            throw new \InvalidArgumentException(\sprintf('Cannot resolve schema with id "%s"', $id));
        }

        return $this->schemas[$id];
    }
}
