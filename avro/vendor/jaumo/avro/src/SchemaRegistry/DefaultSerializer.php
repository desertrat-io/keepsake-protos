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

namespace Avro\SchemaRegistry;

use Avro\Model\Schema\Schema;
use Avro\Model\TypedValue;
use Avro\SchemaRegistry\Model\WireData;
use Avro\Serialization\Message\Serializer as MessageSerializerInterface;
use Avro\Serialization\Schema\Serializer as SchemaSerializerInterface;

final class DefaultSerializer implements Serializer
{
    private AsyncClient $client;
    private Options $options;

    public function __construct(
        AsyncClient $client,
        ?Options $options = null
    ) {
        $this->client = $client;
        $this->options = $options ?? new Options();
    }

    public function serialize(
        string $subject,
        Schema $schema,
        $message,
        MessageSerializerInterface $messageSerializer,
        SchemaSerializerInterface $schemaSerializer
    ): string {
        $serializedSchema = $schemaSerializer->serialize($schema);
        if (null === $id = $this->client->getRegisteredSchemaId($subject, $serializedSchema)) {
            if (!$this->options->isAutoSchemaRegistrationEnabled()) {
                throw ClientError::unknownSchema($subject, $serializedSchema);
            }

            $id = $this->client->registerSchema($subject, $serializedSchema);
        }

        $data = new WireData($id, $messageSerializer->serialize($schema, $message));

        return $data->toBinary();
    }

    public function deserialize(
        string $data,
        ?Schema $schema,
        MessageSerializerInterface $messageSerializer,
        SchemaSerializerInterface $schemaSerializer
    ): TypedValue {
        $messageData = WireData::fromBinary($data);

        if (!$schema instanceof Schema) {
            $schema = $schemaSerializer->deserialize(
                $this->client->getSchema($messageData->getSchemaId())
            );
        }

        return new TypedValue(
            $messageSerializer->deserialize($messageData->getMessage(), $schema),
            $schema
        );
    }
}
