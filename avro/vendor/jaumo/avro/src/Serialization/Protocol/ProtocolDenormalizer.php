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

namespace Avro\Serialization\Protocol;

use Avro\AvroException;
use Avro\Model\Protocol\Message;
use Avro\Model\Protocol\Protocol;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\Named;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\Denormalizer as SchemaDenormalizer;

class ProtocolDenormalizer
{
    private SchemaDenormalizer $schemaDenormalizer;
    private MessageDenormalizer $messageDenormalizer;

    public function __construct(SchemaDenormalizer $schemaDenormalizer, MessageDenormalizer $messageDenormalizer)
    {
        $this->schemaDenormalizer = $schemaDenormalizer;
        $this->messageDenormalizer = $messageDenormalizer;
    }

    /**
     * @param array $data
     * @param Context|null $context
     * @return Protocol
     * @throws AvroException
     */
    public function denormalize(array $data, ?Context $context = null): Protocol
    {
        $context = $context ?? new Context();
        $protocol = Protocol::named($this->denormalizeName($data));

        if (isset($data[Protocol::ATTR_DOC])) {
            $protocol = $protocol->withDoc($data[Protocol::ATTR_DOC]);
        }

        foreach ($this->denormalizeTypes($data, $context) as $type) {
            $protocol = $protocol->withType($type);
        }

        foreach ($this->denormalizeMessages($data, $context) as $message) {
            $protocol = $protocol->withMessage($message);
        }

        return $protocol;
    }

    /**
     * @param array $data
     * @return NamespacedName
     * @throws DenormalizationError
     */
    private function denormalizeName(array $data): NamespacedName
    {
        if (!isset($data[Protocol::ATTR_NAME])) {
            throw DenormalizationError::missingField(Protocol::ATTR_NAME);
        }

        $name = NamespacedName::fromValue($data[Protocol::ATTR_NAME]);
        if (isset($data[Protocol::ATTR_NAMESPACE])) {
            $name = $name->withNamespace($data[Protocol::ATTR_NAMESPACE]);
        }

        return $name;
    }

    /**
     * @param array $data
     * @param Context $context
     * @return Named[]
     * @throws AvroException
     */
    private function denormalizeTypes(array $data, Context $context): array
    {
        if (!isset($data[Protocol::ATTR_TYPES])) {
            return [];
        }

        if (!\is_array($data[Protocol::ATTR_TYPES])) {
            throw DenormalizationError::wrongType(
                Protocol::ATTR_TYPES,
                'array',
                \gettype($data[Protocol::ATTR_TYPES])
            );
        }

        $result = [];
        foreach ($data[Protocol::ATTR_TYPES] as $typeData) {
            /** @var Named $type */
            $type = $this->schemaDenormalizer->denormalize($typeData, Schema::class, $context);
            $result[] = $type;
        }

        return $result;
    }

    /**
     * @param array $data
     * @param Context $context
     * @return Message[]
     * @throws AvroException
     */
    private function denormalizeMessages(array $data, Context $context): array
    {
        if (!isset($data[Protocol::ATTR_MESSAGES])) {
            return [];
        }

        if (!\is_array($data[Protocol::ATTR_MESSAGES])) {
            throw DenormalizationError::wrongType(
                Protocol::ATTR_MESSAGES,
                'array',
                \gettype($data[Protocol::ATTR_MESSAGES])
            );
        }

        $result = [];
        foreach ($data[Protocol::ATTR_MESSAGES] as $name => $messageData) {
            $result[] = $this->messageDenormalizer->denormalize(Name::fromValue($name), $messageData, $context);
        }

        return $result;
    }
}
