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

final class Enum implements Named
{
    public const TYPE = 'enum';
    public const ATTR_SYMBOLS = 'symbols';
    public const ATTR_ALIASES = 'aliases';
    public const ATTR_DOC = 'doc';

    private NamespacedName $name;
    private ?string $doc = null;
    private ?array $aliases = null;
    private array $symbols;

    private function __construct(NamespacedName $name, array $symbols)
    {
        foreach ($symbols as $position => $symbol) {
            if (!$symbol instanceof Name) {
                throw new InvalidSchemaException(\sprintf(
                    'Expected symbols to be an array of "Avro\Model\Name", got "%s" at position %d',
                    \is_object($symbol) ? \get_class($symbol) : \gettype($symbol),
                    $position
                ));
            }
        }

        if (\array_values($symbols) !== $symbols) {
            throw new InvalidSchemaException('Enum symbols is not a list');
        }

        if (\array_unique($symbols) !== $symbols) {
            throw new InvalidSchemaException(\sprintf(
                'Enum symbols must be unique, duplicate found: "%s"',
                \implode(', ', $symbols)
            ));
        }

        $this->name = $name;
        $this->symbols = $symbols;
    }

    public static function named(NamespacedName $name, array $symbols): self
    {
        return new self($name, $symbols);
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

    /**
     * @return Name[]
     */
    public function getSymbols(): array
    {
        return $this->symbols;
    }

    public function getDoc(): ?string
    {
        return $this->doc;
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function getPosition(Name $symbol): int
    {
        if (false === $position = \array_search($symbol, $this->symbols, false)) {
            throw new \InvalidArgumentException(\sprintf(
                'Symbol "%s" cannot be located',
                $symbol
            ));
        }

        return (int) $position;
    }

    public function atPosition(int $index): Name
    {
        if (!isset($this->symbols[$index])) {
            throw new \InvalidArgumentException(\sprintf('There is no symbol at position "%d"', $index));
        }

        return $this->symbols[$index];
    }
}
