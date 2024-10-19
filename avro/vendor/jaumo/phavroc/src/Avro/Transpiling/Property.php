<?php

/*
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

namespace Phavroc\Avro\Transpiling;

use LogicException;

final class Property
{
    private PropertyName $name;
    private PropertyType $type;
    private ?string $doc;
    private bool $nullable = false;
    /** @var mixed $defaultValue */
    private $defaultValue = null;
    private bool $hasDefaultValue = false;

    private function __construct(
        PropertyName $name,
        PropertyType $type,
        ?string $doc
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->doc = $doc;
    }

    public static function fromAvroType(
        PropertyName $name,
        PropertyType $type,
        ?string $doc
    ): self {
        return new self($name, $type, $doc);
    }

    /** @param mixed $defaultValue */
    public function withDefaultValue($defaultValue): self
    {
        $self = clone $this;
        $self->defaultValue = $defaultValue;
        $self->hasDefaultValue = true;

        return $self;
    }

    public function withNullable(bool $nullable): self
    {
        $self = clone $this;
        $self->nullable = $nullable;

        return $self;
    }

    public function withType(string $type): self
    {
        $self = clone $this;
        $self->type = $self->type->withValue($type);

        return $self;
    }

    public function phpName(): string
    {
        return $this->name->toPhpValue();
    }

    public function avroName(): string
    {
        return $this->name->toAvroValue();
    }

    public function type(): string
    {
        return $this->type->value();
    }

    public function doc(): ?string
    {
        return $this->doc;
    }

    public function scalar(): bool
    {
        return $this->type->scalar();
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /** @return mixed */
    public function defaultValue()
    {
        if (!$this->hasDefaultValue()) {
            throw new LogicException('The property does not have a default value');
        }

        return $this->defaultValue;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    public function combinable(): bool
    {
        return $this->type->combinable();
    }

    public function logicalType(): ?string
    {
        return $this->type->logicalType() ? (string) $this->type->logicalType() : null;
    }
}
