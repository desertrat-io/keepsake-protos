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
use Avro\Model\Schema\Reference;
use InvalidArgumentException;

final class Context
{
    private ?string $namespace = null;
    private SchemaRepository $schemaRepository;

    public function __construct()
    {
        $this->schemaRepository = new SchemaRepository();
    }

    public function withNamespace(string $namespace): self
    {
        $self = clone $this;
        $self->namespace = $namespace;

        return $self;
    }

    public function hasNamespace(): bool
    {
        return null !== $this->namespace;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function createReference(Named $schema, ?callable $mutator = null): Reference
    {
        return Reference::create($schema, function (Reference $reference) use ($mutator, $schema) {
            $this->schemaRepository->add($reference);

            // If the reference is not mutated, we can just tell the reference everything is fine
            if (null === $mutator) {
                return $schema;
            }

            return $mutator($schema, $this);
        });
    }

    public function getReference(NamespacedName $name): ?Reference
    {
        return $this->getReferenceByName($name->getFullName());
    }

    public function getReferenceByName(string $name): ?Reference
    {
        try {
            $schema = $this->schemaRepository->resolveByString($name);

            if ($schema instanceof Reference) {
                return $schema;
            }

            return Reference::create($schema);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }
}
