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

final class Enum implements Class_
{
    private ClassName $name;
    private Namespace_ $namespace;
    private ?string $doc;
    private string $schema;
    /** @var string[] */
    private array $options;
    private ?string $interface = null;

    /** @param string[] $options */
    private function __construct(
        ClassName $name,
        Namespace_ $namespace,
        ?string $doc,
        string $schema,
        array $options
    ) {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->doc = $doc;
        $this->schema = $schema;
        $this->options = $options;
    }

    /** @param string[] $options */
    public static function create(
        ClassName $name,
        Namespace_ $namespace,
        ?string $doc,
        string $schema,
        array $options
    ): self {
        return new self($name, $namespace, $doc, $schema, $options);
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

    public function namespace(): string
    {
        return $this->namespace->toString();
    }

    /** @return string[] */
    public function options(): array
    {
        return $this->options;
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
