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

namespace Avro\Model\Schema;

use Avro\AvroException;

final class Reference implements Named
{
    private Named $schema;

    private function __construct(Named $schema)
    {
        if ($schema instanceof self) {
            throw new AvroException('A reference cannot reference another reference');
        }

        $this->schema = $schema;
    }

    public static function create(Named $schema, ?callable $mutator = null): self
    {
        $ref = new self($schema);

        if (null !== $mutator) {
            $updatedSchema = $mutator($ref);

            if (!$updatedSchema instanceof Named) {
                throw new AvroException(\sprintf(
                    'The reference mutator has to return an object of type "%s"',
                    Named::class
                ));
            }

            $ref->schema = $updatedSchema;
        }

        return $ref;
    }

    public function getName(): string
    {
        return $this->schema->getName();
    }

    public function getNamespace(): ?string
    {
        return $this->schema->getNamespace();
    }

    public function getFullName(): string
    {
        return $this->schema->getFullName();
    }

    public function getSchema(): Named
    {
        return $this->schema;
    }
}
