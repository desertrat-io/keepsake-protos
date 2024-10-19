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
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\Normalizer;
use Avro\Serialization\Schema\NormalizerAware;
use Avro\Serialization\Schema\RecordNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RecordNormalizerTest extends TestCase
{
    /** @var RecordNormalizer */
    private $normalizer;

    /** @var Normalizer|MockObject */
    private $delegate;

    public function setUp(): void
    {
        $this->normalizer = new RecordNormalizer();
        $this->delegate = $this->createMock(Normalizer::class);
        $this->delegate
             ->method('normalize')
             ->will($this->returnCallback(function (Schema $schema, bool $canonical) {
                 $classname = \substr(\get_class($schema), \strrpos(\get_class($schema), '\\') + 1);
                 if ($canonical) {
                     return \sprintf('*%s-canonical-data*', $classname);
                 }

                 return \sprintf('*%s-data*', $classname);
             }));
        $this->normalizer->setNormalizer($this->delegate);
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Normalizer::class, $this->normalizer);
        $this->assertInstanceOf(NormalizerAware::class, $this->normalizer);
    }

    /**
     * @dataProvider canonicalExamples
     *
     * @param Record $schema
     */
    public function testSupporting(Record $schema): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($schema));
    }

    public function testNotSupportingNonRecord(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new class() implements Schema {
        }));
    }

    /**
     * @dataProvider nonCanonicalExamples
     *
     * @param Record $schema
     * @param mixed $normalized
     * @throws NormalizationError
     */
    public function testNormalizing(Record $schema, $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema));
    }

    /**
     * @dataProvider canonicalExamples
     *
     * @param Record $schema
     * @param array $normalized
     * @throws NormalizationError
     */
    public function testCanonicalNormalizing(Record $schema, array $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema, true));
    }

    /**
     * @throws NormalizationError
     */
    public function testNormalizingWithContextualNamespace(): void
    {
        $this->assertSame(
            [Record::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Record::TYPE, Record::ATTR_FIELDS => []],
            $this->normalizer->normalize(
                Record::named(NamespacedName::fromValue('com.avro.foo')),
                false,
                (new Context())->withNamespace('com.avro')
            )
        );

        $this->assertSame(
            [Record::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Record::TYPE, Record::ATTR_FIELDS => [], Record::ATTR_NAMESPACE => 'org.avro'],
            $this->normalizer->normalize(
                Record::named(NamespacedName::fromValue('org.avro.foo')),
                false,
                (new Context())->withNamespace('com.avro')
            )
        );
    }

    public function nonCanonicalExamples(): array
    {
        return [
            [
                Record::named(NamespacedName::fromValue('Foo'))
                    ->withDoc('lorem')
                    ->withNamespace('com.avro')
                    ->withAliases(['bar', 'baz'])
                    ->withAddedField(
                        RecordField::named(Name::fromValue('field1'), Primitive::int())
                    ),
                [
                    Record::ATTR_NAME => 'Foo',
                    Schema::ATTR_TYPE => Record::TYPE,
                    Record::ATTR_FIELDS => ['*RecordField-data*'],
                    Record::ATTR_NAMESPACE => 'com.avro',
                    Record::ATTR_ALIASES => ['bar', 'baz'],
                    Record::ATTR_DOC => 'lorem',
                ],
            ],
            [
                Record::namedError(NamespacedName::fromValue('Foo'))
                    ->withDoc('lorem')
                    ->withNamespace('com.avro')
                    ->withAliases(['bar', 'baz'])
                    ->withAddedField(
                        RecordField::named(Name::fromValue('field1'), Primitive::int())
                    ),
                [
                    Record::ATTR_NAME => 'Foo',
                    Schema::ATTR_TYPE => Record::TYPE_ERROR,
                    Record::ATTR_FIELDS => ['*RecordField-data*'],
                    Record::ATTR_NAMESPACE => 'com.avro',
                    Record::ATTR_ALIASES => ['bar', 'baz'],
                    Record::ATTR_DOC => 'lorem',
                ],
            ],
        ];
    }

    public function canonicalExamples(): array
    {
        return [
            [
                Record::named(NamespacedName::fromValue('Foo'))
                    ->withDoc('lorem')
                    ->withNamespace('com.avro')
                    ->withAliases(['bar', 'baz'])
                    ->withAddedField(
                        RecordField::named(Name::fromValue('field1'), Primitive::int())
                    ),
                [
                    Record::ATTR_NAME => 'com.avro.Foo',
                    Schema::ATTR_TYPE => Record::TYPE,
                    Record::ATTR_FIELDS => ['*RecordField-canonical-data*'],
                ],
            ],
            [
                Record::namedError(NamespacedName::fromValue('Foo'))
                    ->withDoc('lorem')
                    ->withNamespace('com.avro')
                    ->withAliases(['bar', 'baz'])
                    ->withAddedField(
                        RecordField::named(Name::fromValue('field1'), Primitive::int())
                    ),
                [
                    Record::ATTR_NAME => 'com.avro.Foo',
                    Schema::ATTR_TYPE => Record::TYPE_ERROR,
                    Record::ATTR_FIELDS => ['*RecordField-canonical-data*'],
                ],
            ],
        ];
    }
}
