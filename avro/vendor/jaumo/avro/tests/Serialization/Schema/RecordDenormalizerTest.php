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

namespace AvroTest\Serialization\Schema;

use Avro\Model\Schema\Name;
use Avro\Model\Schema\Named;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Reference;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\Denormalizer;
use Avro\Serialization\Schema\DenormalizerAware;
use Avro\Serialization\Schema\RecordDenormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RecordDenormalizerTest extends TestCase
{
    /** @var RecordDenormalizer */
    private $denormalizer;

    /** @var Denormalizer|MockObject */
    private $delegated;

    public function setUp(): void
    {
        $this->denormalizer = new RecordDenormalizer();
        $this->delegated = $this->createMock(Denormalizer::class);

        $this->denormalizer->setDenormalizer($this->delegated);
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Denormalizer::class, $this->denormalizer);
        $this->assertInstanceOf(DenormalizerAware::class, $this->denormalizer);
    }

    /**
     * @dataProvider recordTypeExamples
     *
     * @param array $data
     */
    public function testSupporting(array $data): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization($data, Schema::class));
        $this->assertTrue($this->denormalizer->supportsDenormalization($data, Record::class));
    }

    /**
     * @dataProvider recordTypeExamples
     *
     * @param array $data
     * @param Named $schema
     * @param string $id
     * @throws DenormalizationError
     */
    public function testDenormalizing(array $data, Named $schema, string $id): void
    {
        $context = new Context();

        $this->assertEquals($schema, $this->denormalizer->denormalize($data, Schema::class, $context));
        $this->assertEquals(Reference::create($schema), $context->getReferenceByName($schema->getFullName()));
    }

    /**
     * @dataProvider namespaceDefinitionExamples
     */
    public function testNamespaceDefinition(array $schema, Context $context, Schema $result): void
    {
        $this->assertEquals(
            $result,
            $this->denormalizer->denormalize($schema, Record::class, $context)
        );
    }

    /**
     * @dataProvider recordTypeExamples
     * @param array $data
     *
     * @throws DenormalizationError
     */
    public function testDenormalizingErrorRecords(array $data): void
    {
        /** @var Record $record */
        $record = $this->denormalizer->denormalize($data);
        $expectedIsError = isset($data[Schema::ATTR_TYPE]) && $data[Schema::ATTR_TYPE] === Record::TYPE_ERROR;

        $this->assertEquals($expectedIsError, $record->isError());
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithFields(): void
    {
        $field = RecordField::named(Name::fromValue('foo'), Primitive::int());
        $this->delegated
             ->method('denormalize')
             ->with(
                 $this->equalTo([Schema::ATTR_TYPE => Primitive::TYPE_INT, Record::ATTR_NAME => 'foo']),
                 $this->identicalTo(RecordField::class)
             )
            ->willReturn($field);

        $this->assertEquals(
            Record::named(NamespacedName::fromValue('Message'))->withAddedField($field),
            $this->denormalizer->denormalize([
                Schema::ATTR_TYPE => Record::TYPE,
                Record::ATTR_NAME => 'Message',
                Record::ATTR_FIELDS => [
                    [Record::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Primitive::TYPE_INT],
                ],
            ])
        );
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithoutNameField(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Schema::ATTR_TYPE => Record::TYPE]);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithoutFields(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Schema::ATTR_TYPE => Record::TYPE, Record::ATTR_NAME => 'Message']);
    }

    public function recordTypeExamples(): array
    {
        return [
            [
                [Schema::ATTR_TYPE => Record::TYPE, Record::ATTR_NAME => 'Message', Record::ATTR_FIELDS => []],
                Record::named(NamespacedName::fromValue('Message')),
                'Message',
            ],
            [
                [Schema::ATTR_TYPE => Record::TYPE, Record::ATTR_NAME => 'Message', Record::ATTR_NAMESPACE => 'com.avro', Record::ATTR_FIELDS => []],
                Record::named(NamespacedName::fromValue('Message'))->withNamespace('com.avro'),
                'com.avro.Message',
            ],
            [
                [Schema::ATTR_TYPE => Record::TYPE, Record::ATTR_NAME => 'com.avro.Message', Record::ATTR_FIELDS => []],
                Record::named(NamespacedName::fromValue('com.avro.Message')),
                'com.avro.Message',
            ],
            [
                [Schema::ATTR_TYPE => Record::TYPE, Record::ATTR_NAME => 'Message', Record::ATTR_DOC => 'A sample message', Record::ATTR_FIELDS => []],
                Record::named(NamespacedName::fromValue('Message'))->withDoc('A sample message'),
                'Message',
            ],
            [
                [Schema::ATTR_TYPE => Record::TYPE, Record::ATTR_NAME => 'Message', Record::ATTR_ALIASES => ['OldName'], Record::ATTR_FIELDS => []],
                Record::named(NamespacedName::fromValue('Message'))->withAliases(['OldName']),
                'Message',
            ],
            [
                [
                    Schema::ATTR_TYPE => Record::TYPE_ERROR,
                    Record::ATTR_NAME => 'Message',
                    Record::ATTR_ALIASES => ['OldName'],
                    Record::ATTR_FIELDS => [],
                ],
                Record::namedError(NamespacedName::fromValue('Message'))->withAliases(['OldName']),
                'Message',
            ],
        ];
    }

    public function namespaceDefinitionExamples(): array
    {
        return [
            [
                [Record::ATTR_NAME => 'Message', Record::ATTR_FIELDS => []],
                new Context(),
                Record::named(NamespacedName::fromValue('Message')),
            ],
            [
                [Record::ATTR_NAME => 'Message', Record::ATTR_FIELDS => []],
                (new Context())->withNamespace('com.context.avro'),
                Record::named(NamespacedName::fromValue('com.context.avro.Message')),
            ],
            [
                [Record::ATTR_NAME => 'Message', Record::ATTR_FIELDS => [], Record::ATTR_NAMESPACE => 'com.avro'],
                new Context(),
                Record::named(NamespacedName::fromValue('com.avro.Message')),
            ],
            [
                [Record::ATTR_NAME => 'Message', Record::ATTR_FIELDS => [], Record::ATTR_NAMESPACE => 'com.avro'],
                (new Context())->withNamespace('com.context.avro'),
                Record::named(NamespacedName::fromValue('com.avro.Message')),
            ],
            [
                [Record::ATTR_NAME => 'com.avro.Message', Record::ATTR_FIELDS => []],
                new Context(),
                Record::named(NamespacedName::fromValue('com.avro.Message')),
            ],
            [
                [Record::ATTR_NAME => 'com.avro.Message', Record::ATTR_FIELDS => []],
                (new Context())->withNamespace('com.context.avro'),
                Record::named(NamespacedName::fromValue('com.avro.Message')),
            ],
            [
                [Record::ATTR_NAME => 'com.avro.Message', Record::ATTR_FIELDS => [], Record::ATTR_NAMESPACE => 'org.avro'],
                new Context(),
                Record::named(NamespacedName::fromValue('com.avro.Message')),
            ],
            [
                [Record::ATTR_NAME => 'com.avro.Message', Record::ATTR_FIELDS => [], Record::ATTR_NAMESPACE => 'org.avro'],
                (new Context())->withNamespace('com.context.avro'),
                Record::named(NamespacedName::fromValue('com.avro.Message')),
            ],
        ];
    }
}
