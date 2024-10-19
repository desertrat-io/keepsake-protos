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

namespace Avro\Model\Protocol;

use Avro\Model\Schema\Named;
use Avro\Model\Schema\NamespacedName;
use InvalidArgumentException;

final class Protocol
{
    public const ATTR_NAME = 'protocol';
    public const ATTR_NAMESPACE = 'namespace';
    public const ATTR_DOC = 'doc';
    public const ATTR_TYPES = 'types';
    public const ATTR_MESSAGES = 'messages';

    private NamespacedName $name;

    private ?string $hash = null;
    private ?string $doc;

    /** @var Named[] */
    protected array $types = [];

    /** @var Message[] */
    protected array $messages = [];

    private function __construct(NamespacedName $name)
    {
        $this->name = $name;
    }

    public static function named(NamespacedName $name): self
    {
        return new self($name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name->getName();
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->name->getNamespace();
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->name->getFullName();
    }

    /**
     * @return string|null
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function withHash(string $hash): self
    {
        $self = clone $this;
        $self->hash = $hash;

        return $self;
    }

    /**
     * @return string|null
     */
    public function getDoc(): ?string
    {
        return $this->doc;
    }

    public function withDoc(string $doc): self
    {
        $newSelf = clone $this;
        $newSelf->doc = $doc;

        return $newSelf;
    }

    /**
     * @return Named[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function withType(Named $type): self
    {
        if ($type->getNamespace() !== null) {
            throw new InvalidArgumentException('Protocol Types cannot have an own namespace');
        }

        $newSelf = clone $this;
        $newSelf->types[$type->getName()] = $type;

        return $newSelf;
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function withMessage(Message $message): self
    {
        $newSelf = clone $this;
        $newSelf->messages[$message->getName()->getValue()] = $message;

        return $newSelf;
    }
}
