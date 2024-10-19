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

final class DTO implements Class_
{
    private ClassName $name;
    private Namespace_ $namespace;
    private ?string $doc;
    private string $schema;
    /** @var Property[] */
    private array $properties = [];
    private ?string $interface = null;

    private function __construct(
        ClassName $name,
        Namespace_ $namespace,
        ?string $doc,
        string $schema
    ) {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->doc = $doc;
        $this->schema = $schema;
    }

    public static function create(
        ClassName $name,
        Namespace_ $namespace,
        ?string $doc,
        string $schema
    ): self {
        return new self($name, $namespace, $doc, $schema);
    }

    public function withAddedProperty(Property $property): self
    {
        $self = clone $this;
        $self->properties[] = $property;

        return $self;
    }

    public function name(): string
    {
        return $this->name->toString();
    }

    public function doc(): ?string
    {
        return $this->doc;
    }

    public function schema(): string
    {
        return $this->schema;
    }

    /** @return Property[] */
    public function properties(): array
    {
        return $this->properties;
    }

    public function namespace(): string
    {
        return $this->namespace->toString();
    }

    public function fqcn(): string
    {
        return sprintf('%s\\%s', $this->namespace, $this->name);
    }

    public function interface(): ?string
    {
        return $this->interface;
    }

    public function equals(Class_ $class): bool
    {
        return $class instanceof self
            && $this->namespace->equals($class->namespace)
            && $this->name->equals($class->name);
    }

    public function withInterface(string $interface): self
    {
        $self = clone $this;
        $self->interface = $interface;

        return $self;
    }
}
