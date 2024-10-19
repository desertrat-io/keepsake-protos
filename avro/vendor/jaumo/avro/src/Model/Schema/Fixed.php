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

final class Fixed implements Named
{
    public const TYPE = 'fixed';
    public const ATTR_SIZE = 'size';
    public const ATTR_ALIASES = 'aliases';

    private NamespacedName $name;
    private int $size;
    private ?array $aliases = null;
    private ?LogicalType $logicalType = null;

    private function __construct(NamespacedName $name, int $size)
    {
        $this->name = $name;
        $this->size = $size;
    }

    public static function named(NamespacedName $name, int $size): self
    {
        return new self($name, $size);
    }

    public static function duration(NamespacedName $name): self
    {
        $self = new self($name, 12);
        $self->logicalType = LogicalType::named(self::LOGICAL_TYPE_DURATION);

        return $self;
    }

    public function withNamespace(string $namespace): self
    {
        $self = clone $this;
        $self->name = $this->name->withNamespace($namespace);

        return $self;
    }

    public function withAliases(array $aliases): self
    {
        $self = clone $this;
        $self->aliases = $aliases;

        return $self;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getFullName(): string
    {
        return $this->name->getFullName();
    }

    public function getName(): string
    {
        return $this->name->getName();
    }

    public function getNamespace(): ?string
    {
        return $this->name->getNamespace();
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function getLogicalType(): ?LogicalType
    {
        return $this->logicalType;
    }
}
