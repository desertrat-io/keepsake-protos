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

use Avro\Model\Schema\Name;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;

final class Message
{
    public const TYPE = 'message';
    public const ATTR_DOC = 'doc';
    public const ATTR_IS_ONE_WAY = 'one-way';
    public const ATTR_REQUEST = 'request';
    public const ATTR_RESPONSE = 'response';
    public const ATTR_ERRORS = 'errors';

    private Name $name;
    private ?string $doc;
    private Request $request;
    private ?Schema $response;
    private ?Union $errors;

    private function __construct(Name $name, Request $request, ?Schema $response, ?Union $errors)
    {
        $this->name = $name;

        $this->request = $request;
        $this->response = $response;
        $this->errors = $errors;
    }

    public static function oneWay(Name $name, Request $request): self
    {
        return new self($name, $request, null, null);
    }

    public static function twoWay(Name $name, Request $request, Schema $response, ?Union $errors = null): self
    {
        return new self($name, $request, $response, $errors);
    }

    /**
     * @return Name
     */
    public function getName(): Name
    {
        return $this->name;
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
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return null|Schema
     */
    public function getResponse(): ?Schema
    {
        return $this->response;
    }

    /**
     * @return null|Union
     */
    public function getErrors(): ?Union
    {
        return $this->errors;
    }

    public function getEffectiveErrors(): Union
    {
        $types = $this->errors ? $this->errors->getTypes() : [];
        \array_unshift($types, Primitive::string());

        return Union::of($types);
    }

    public function isOneWay(): bool
    {
        return $this->errors === null && $this->response === null;
    }
}
