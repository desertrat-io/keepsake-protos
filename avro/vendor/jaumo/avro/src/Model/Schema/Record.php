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

final class Record implements Named
{
    public const TYPE = 'record';

    public const TYPE_ERROR = 'error';

    public const ATTR_FIELDS = 'fields';

    public const ATTR_ALIASES = 'aliases';

    public const ATTR_DOC = 'doc';

    private NamespacedName $name;

    private ?string $doc = null;

    private ?array $aliases = null;

    private array $fields = [];

    private bool $isError;

    private function __construct(NamespacedName $name, bool $isError)
    {
        $this->name = $name;
        $this->isError = $isError;
    }

    public static function named(NamespacedName $name): self
    {
        return new self($name, false);
    }

    public static function namedError(NamespacedName $name): self
    {
        return new self($name, true);
    }

    public function withNamespace(string $namespace): self
    {
        $self = clone $this;
        $self->name = $this->name->withNamespace($namespace);

        return $self;
    }

    public function withDoc(string $doc): self
    {
        $self = clone $this;
        $self->doc = $doc;

        return $self;
    }

    public function withAliases(array $aliases): self
    {
        $self = clone $this;
        $self->aliases = $aliases;

        return $self;
    }

    public function withAddedField(RecordField $field): self
    {
        $self = clone $this;
        $self->fields[] = $field;

        return $self;
    }

    /**
     * @return RecordField[]
     */
    public function getFields(): array
    {
        return $this->fields;
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

    public function getDoc(): ?string
    {
        return $this->doc;
    }

    public function isError(): bool
    {
        return $this->isError;
    }
}
