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

final class NamespacedName
{
    private Name $name;
    private ?string $namespace;

    private function __construct(Name $name, ?string $namespace)
    {
        $this->name = $name;
        $this->namespace = $namespace;
    }

    public static function fromValue(string $value): self
    {
        $position = \strrpos($value, '.');
        if (false === $position) {
            return new self(Name::fromValue($value), null);
        }

        $namespace = \substr($value, 0, $position);
        $name = Name::fromValue(\substr($value, $position + 1));

        return new self($name, $namespace);
    }

    public static function fromName(Name $name, string $namespace = null): self
    {
        return new self($name, $namespace);
    }

    public function withNamespace(string $namespace): self
    {
        $self = clone $this;
        $self->namespace = $namespace;

        return $self;
    }

    public function getName(): string
    {
        return $this->name->getValue();
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getFullName(): string
    {
        return (null !== $this->namespace ? $this->namespace.'.' : '').$this->getName();
    }
}
