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
use Avro\Model\Protocol\Request;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\Denormalizer as SchemaDenormalizer;

class MessageDenormalizer
{
    private SchemaDenormalizer $schemaDenormalizer;

    public function __construct(SchemaDenormalizer $schemaDenormalizer)
    {
        $this->schemaDenormalizer = $schemaDenormalizer;
    }

    /**
     * @param Name $name
     * @param array $data
     * @param Context $context
     * @return Message
     * @throws AvroException
     */
    public function denormalize(Name $name, array $data, Context $context): Message
    {
        if (!isset($data[Message::ATTR_REQUEST])) {
            throw DenormalizationError::missingField(Message::ATTR_REQUEST);
        }

        if (!\is_array($data[Message::ATTR_REQUEST])) {
            throw DenormalizationError::wrongType(Message::ATTR_REQUEST, 'array', \gettype($data[Message::ATTR_REQUEST]));
        }

        $request = $this->denormalizeRequest($data[Message::ATTR_REQUEST], $context);

        $response = null;
        if (isset($data[Message::ATTR_RESPONSE])) {
            if (\is_string($data[Message::ATTR_RESPONSE])) {
                $data[Message::ATTR_RESPONSE] = [Schema::ATTR_TYPE => $data[Message::ATTR_RESPONSE]];
            }

            /** @var Schema $response */
            $response = $this->schemaDenormalizer->denormalize($data[Message::ATTR_RESPONSE], Schema::class, $context);
        }

        $errors = null;
        if (isset($data[Message::ATTR_ERRORS])) {
            /** @var Union $errors */
            $errors = $this->schemaDenormalizer->denormalize($data[Message::ATTR_ERRORS], Union::class, $context);
        }

        if ($response !== null) {
            if (($data[Message::ATTR_IS_ONE_WAY] ?? false) === true) {
                throw new DenormalizationError('A one-way message cannot have a response');
            }

            $message = Message::twoWay($name, $request, $response, $errors);
        } else {
            if ($errors !== null) {
                throw new DenormalizationError('A one-way message cannot have errors');
            }

            $message = Message::oneWay($name, $request);
        }

        if (isset($data[Message::ATTR_DOC])) {
            $message = $message->withDoc($data[Message::ATTR_DOC]);
        }

        return $message;
    }

    /**
     * @param array $data
     * @param Context $context
     * @return Request
     * @throws AvroException
     */
    private function denormalizeRequest(array $data, Context $context): Request
    {
        $params = [];

        foreach ($data as $rawParam) {
            $params[] = $this->schemaDenormalizer->denormalize($rawParam, RecordField::class, $context);
        }

        /* @phpstan-ignore-next-line */
        return Request::ofParameters($params);
    }
}
