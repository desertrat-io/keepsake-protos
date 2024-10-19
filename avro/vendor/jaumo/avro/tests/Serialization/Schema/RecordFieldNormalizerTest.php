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
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\RecordFieldDefault;
use Avro\Model\Schema\RecordFieldOrder;
use Avro\Model\Schema\Schema;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\Normalizer;
use Avro\Serialization\Schema\NormalizerAware;
use Avro\Serialization\Schema\RecordFieldNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RecordFieldNormalizerTest extends TestCase
{
    /**
     * @var RecordFieldNormalizer
     */
    private $normalizer;

    /**
     * @var Normalizer|MockObject
     */
    private $delegate;

    public function setUp(): void
    {
        $this->normalizer = new RecordFieldNormalizer();
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
     * @param RecordField $schema
     */
    public function testSupporting(RecordField $schema): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($schema));
    }

    public function testNotSupportingNonRecordField(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new class() implements Schema {
        }));
    }

    /**
     * @dataProvider nonCanonicalExamples
     *
     * @param RecordField $schema
     * @param mixed $normalized
     * @throws NormalizationError
     */
    public function testNormalizing(RecordField $schema, $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema));
    }

    /**
     * @dataProvider canonicalExamples
     *
     * @param RecordField $schema
     * @param array $normalized
     * @throws NormalizationError
     */
    public function testCanonicalNormalizing(RecordField $schema, array $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema, true));
    }

    public function nonCanonicalExamples(): array
    {
        return [
            [
                RecordField::named(Name::fromValue('field1'), Primitive::int())
                    ->withDefault(RecordFieldDefault::fromValue(42))
                    ->withDoc('lorem')
                    ->withOrder(RecordFieldOrder::fromValue(RecordFieldOrder::ASCENDING))
                    ->withAliases(['bar', 'baz']),
                [
                    RecordField::ATTR_NAME => 'field1',
                    RecordField::ATTR_TYPE => '*Primitive-data*',
                    RecordField::ATTR_DEFAULT => 42,
                    RecordField::ATTR_DOC => 'lorem',
                    RecordField::ATTR_ORDER => RecordFieldOrder::ASCENDING,
                    RecordField::ATTR_ALIASES => ['bar', 'baz'],
                ],
            ],
        ];
    }

    public function canonicalExamples(): array
    {
        return [
            [
                RecordField::named(Name::fromValue('field1'), Primitive::int())
                    ->withDefault(RecordFieldDefault::fromValue(42))
                    ->withDoc('lorem')
                    ->withOrder(RecordFieldOrder::fromValue(RecordFieldOrder::ASCENDING))
                    ->withAliases(['bar', 'baz']),
                [
                    RecordField::ATTR_NAME => 'field1',
                    RecordField::ATTR_TYPE => '*Primitive-canonical-data*',
                ],
            ],
        ];
    }
}
