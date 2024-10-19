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

use Avro\Validation\Validating;

final class RecordField implements Schema
{
    public const ATTR_NAME = 'name';
    public const ATTR_DOC = 'doc';
    public const ATTR_DEFAULT = 'default';
    public const ATTR_ORDER = 'order';
    public const ATTR_ALIASES = 'aliases';

    private Name $name;
    private Schema $type;
    private ?string $doc = null;
    private ?RecordFieldDefault $default = null;
    private ?RecordFieldOrder $order = null;
    private ?array $aliases = null;

    private function __construct(Name $name, Schema $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function named(Name $name, Schema $type): self
    {
        return new self($name, $type);
    }

    public function withDoc(string $doc): self
    {
        $self = clone $this;
        $self->doc = $doc;

        return $self;
    }

    public function withDefault(RecordFieldDefault $default): self
    {
        $type = $this->type;
        if ($this->type instanceof Union) {
            $type = $this->type->getTypes()[0] ?? null;
        }

        if ($type instanceof Schema) {
            $value = $default->getValue();
            if (!Validating::isValid($value, $type)) {
                throw new InvalidSchemaException(\sprintf(
                    'Default value "%s(%s)" is invalid',
                    \get_class($type),
                    \var_export($value, true)
                ));
            }
        }

        $self = clone $this;
        $self->default = $default;

        return $self;
    }

    public function withOrder(RecordFieldOrder $order): self
    {
        $self = clone $this;
        $self->order = $order;

        return $self;
    }

    public function withAliases(array $aliases): self
    {
        $self = clone $this;
        $self->aliases = $aliases;

        return $self;
    }

    public function getName(): string
    {
        return $this->name->getValue();
    }

    public function getType(): Schema
    {
        return $this->type;
    }

    public function getDefault(): ?RecordFieldDefault
    {
        return $this->default;
    }

    public function getDoc(): ?string
    {
        return $this->doc;
    }

    public function getOrder(): ?RecordFieldOrder
    {
        return $this->order;
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }
}
