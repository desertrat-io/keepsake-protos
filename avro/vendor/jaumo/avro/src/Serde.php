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

namespace Avro;

use Avro\Model\Protocol\Protocol;
use Avro\Model\Schema\Schema;
use Avro\Model\TypedValue;
use Avro\SchemaRegistry\Serializer as SchemaRegistrySerializerInterface;
use Avro\Serialization\Message\DefaultSerializer as MessageSerializer;
use Avro\Serialization\Message\Serializer as MessageSerializerInterface;
use Avro\Serialization\Protocol\DefaultSerializer as ProtocolSerializer;
use Avro\Serialization\Protocol\MessageDenormalizer;
use Avro\Serialization\Protocol\MessageNormalizer;
use Avro\Serialization\Protocol\ProtocolDenormalizer;
use Avro\Serialization\Protocol\ProtocolNormalizer;
use Avro\Serialization\Protocol\Serializer as ProtocolSerializerInterface;
use Avro\Serialization\Schema\Serializer as SchemaSerializerInterface;

final class Serde
{
    private static SchemaSerializerInterface $schemaSerializer;
    private static ProtocolSerializerInterface $protocolSerializer;
    private static MessageSerializerInterface $messageSerializer;

    private function __construct()
    {
    }

    public static function encodeMessage(Schema $schema, $message): string
    {
        self::init();

        return self::$messageSerializer->serialize($schema, $message);
    }

    public static function encodeMessageWithSchemaRegistry(
        Schema $schema,
        $message,
        string $subject,
        SchemaRegistrySerializerInterface $serializer
    ): string {
        self::init();

        return $serializer->serialize(
            $subject,
            $schema,
            $message,
            self::$messageSerializer,
            self::$schemaSerializer
        );
    }

    public static function decodeMessageWithSchemaRegistry(
        string $message,
        SchemaRegistrySerializerInterface $serializer,
        ?Schema $schema = null
    ): TypedValue {
        self::init();

        return $serializer->deserialize(
            $message,
            $schema,
            self::$messageSerializer,
            self::$schemaSerializer
        );
    }

    public static function decodeMessage(Schema $schema, string $message)
    {
        self::init();

        return self::$messageSerializer->deserialize($message, $schema);
    }

    public static function dumpSchema(Schema $schema): string
    {
        self::init();

        return self::$schemaSerializer->serialize($schema);
    }

    public static function dumpCanonicalSchema(Schema $schema): string
    {
        self::init();

        return self::$schemaSerializer->serialize($schema, true);
    }

    public static function parseSchema(string $json): Schema
    {
        self::init();

        return self::$schemaSerializer->deserialize($json);
    }

    public static function dumpProtocol(Protocol $protocol): string
    {
        self::init();

        return self::$protocolSerializer->serialize($protocol);
    }

    public static function parseProtocol(string $json): Protocol
    {
        self::init();

        return self::$protocolSerializer->deserialize($json);
    }

    private static function init(): void
    {
        if (!isset(self::$schemaSerializer)) {
            self::$schemaSerializer = new SchemaSerializer();
        }

        if (!isset(self::$protocolSerializer)) {
            self::$protocolSerializer = new ProtocolSerializer(
                new ProtocolNormalizer(
                    self::$schemaSerializer,
                    new MessageNormalizer(self::$schemaSerializer)
                ),
                new ProtocolDenormalizer(
                    self::$schemaSerializer,
                    new MessageDenormalizer(self::$schemaSerializer)
                )
            );
        }

        if (!isset(self::$messageSerializer)) {
            self::$messageSerializer = new MessageSerializer();
        }
    }
}
