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

namespace AvroTest\Integration;

use Amp\Http\Client\HttpClientBuilder;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\SchemaRegistry\AmpClient;
use Avro\SchemaRegistry\ClientError;
use Avro\SchemaRegistry\DefaultSerializer;
use Avro\SchemaRegistry\Options;
use Avro\Serde;
use PHPUnit\Framework\TestCase;

final class SchemaRegistrySerializerTest extends TestCase
{
    private AmpClient $client;

    protected function setUp(): void
    {
        if (false === $baseUri = \getenv('SCHEMA_REGISTRY_BASE_URI')) {
            $this->markTestSkipped(
                'Environment variable SCHEMA_REGISTRY_BASE_URI is not set, please check the phpunit.xml file'
            );
        }

        $this->client = new AmpClient((string) $baseUri, HttpClientBuilder::buildDefault());
    }

    public function testSerializeWithRegisteredSchema(): void
    {
        $subject = \uniqid('schema_', true);
        $schema = Record::named(NamespacedName::fromValue(\sprintf('%s.com.avro.Person', $subject)))
            ->withAddedField(RecordField::named(Name::fromValue('name'), Primitive::string()));
        $schemaId = $this->client->registerSchema($subject, Serde::dumpSchema($schema));

        $data = Serde::encodeMessageWithSchemaRegistry(
            $schema,
            ['name' => 'John Doe'],
            $subject,
            new DefaultSerializer($this->client)
        );

        list('magic' => $magic, 'id' => $id) = \unpack('Cmagic/Nid/A*avro', $data);
        $this->assertSame(0, $magic);
        $this->assertSame($schemaId, $id);
    }

    public function testSerializeWithAutoRegisteredSchema(): void
    {
        $subject = \uniqid('schema_', true);
        $schema = Record::named(NamespacedName::fromValue(\sprintf('%s.com.avro.Person', $subject)))
            ->withAddedField(RecordField::named(Name::fromValue('name'), Primitive::string()));

        $data = Serde::encodeMessageWithSchemaRegistry(
            $schema,
            ['name' => 'John Doe'],
            $subject,
            new DefaultSerializer($this->client, (new Options())->enableAutoSchemaRegistration())
        );

        list('magic' => $magic, 'id' => $id) = \unpack('Cmagic/Nid/A*avro', $data);
        $this->assertSame(0, $magic);
        $this->assertGreaterThan(0, $id);
    }

    public function testSerializeWithUnregisteredSchema(): void
    {
        $this->expectException(ClientError::class);

        $subject = \uniqid('schema_', true);
        $schema = Record::named(NamespacedName::fromValue(\sprintf('%s.com.avro.Person', $subject)))
            ->withAddedField(RecordField::named(Name::fromValue('name'), Primitive::string()));

        Serde::encodeMessageWithSchemaRegistry(
            $schema,
            ['name' => 'John Doe'],
            $subject,
            new DefaultSerializer($this->client)
        );
    }

    public function testDeserializeWithReadSchema(): void
    {
        $serializer = new DefaultSerializer($this->client);

        $writeSchema = Record::named(NamespacedName::fromValue('com.avro.Person'))
            ->withAddedField(RecordField::named(Name::fromValue('name'), Primitive::string()));
        $subject = \uniqid('schema_', true);
        $this->client->registerSchema($subject, Serde::dumpSchema($writeSchema));
        $data = Serde::encodeMessageWithSchemaRegistry(
            $writeSchema,
            ['name' => 'John Doe'],
            $subject,
            $serializer
        );

        $readSchema = Record::named(NamespacedName::fromValue('com.avro.Person'))
            ->withAddedField(RecordField::named(Name::fromValue('name'), Primitive::string()));

        $message = Serde::decodeMessageWithSchemaRegistry(
            $data,
            $serializer,
            $readSchema
        );

        $this->assertSame('John Doe', $message->getValue()['name']);
    }

    public function testDeserializeWithKnownWriteSchema(): void
    {
        $serializer = new DefaultSerializer($this->client);

        $writeSchema = Record::named(NamespacedName::fromValue('com.avro.Person'))
            ->withAddedField(RecordField::named(Name::fromValue('name'), Primitive::string()));
        $subject = \uniqid('schema_', true);
        $this->client->registerSchema($subject, Serde::dumpSchema($writeSchema));
        $data = Serde::encodeMessageWithSchemaRegistry(
            $writeSchema,
            ['name' => 'John Doe'],
            $subject,
            $serializer
        );

        $message = Serde::decodeMessageWithSchemaRegistry(
            $data,
            $serializer
        );

        $this->assertEquals($writeSchema, $message->getSchema());
        $this->assertSame('John Doe', $message->getValue()['name']);
    }

    public function testDeserializeWithUnknownWriteSchema(): void
    {
        $serializer = new DefaultSerializer($this->client);

        $writeSchema = Record::named(NamespacedName::fromValue('com.avro.Person'))
            ->withAddedField(RecordField::named(Name::fromValue('name'), Primitive::string()));
        $subject = \uniqid('schema_', true);
        $this->client->registerSchema($subject, Serde::dumpSchema($writeSchema));
        $data = Serde::encodeMessageWithSchemaRegistry(
            $writeSchema,
            ['name' => 'John Doe'],
            $subject,
            $serializer
        );

        $message = Serde::decodeMessageWithSchemaRegistry(
            $data,
            $serializer
        );

        $this->assertEquals($writeSchema, $message->getSchema());
        $this->assertSame('John Doe', $message->getValue()['name']);
    }
}
