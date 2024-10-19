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

use Avro\Model\Schema\LogicalType;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\Normalizer;
use Avro\Serialization\Schema\NormalizerAware;
use Avro\Serialization\Schema\PrimitiveNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PrimitiveNormalizerTest extends TestCase
{
    /**
     * @var PrimitiveNormalizer
     */
    private $normalizer;

    /** @var Normalizer|MockObject */
    private $delegate;

    public function setUp(): void
    {
        $this->normalizer = new PrimitiveNormalizer();
        $this->delegate = $this->createMock(Normalizer::class);
        $this->delegate->method('normalize')->with($this->isInstanceOf(LogicalType::class))->willReturn(['foo' => 'bar']);
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
     * @param Primitive $schema
     */
    public function testSupporting(Primitive $schema): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($schema));
    }

    public function testNotSupportingNonPrimitive(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new class() implements Schema {
        }));
    }

    /**
     * @dataProvider nonCanonicalExamples
     *
     * @param Primitive $schema
     * @param mixed $normalized
     * @throws NormalizationError
     */
    public function testNormalizing(Primitive $schema, $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema));
    }

    /**
     * @dataProvider canonicalExamples
     * @param Primitive $schema
     * @param array $normalized
     * @throws NormalizationError
     */
    public function testCanonicalNormalizing(Primitive $schema, array $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema, true));
    }

    public function nonCanonicalExamples(): array
    {
        $examples = [];

        foreach (Primitive::TYPES as $type) {
            $examples[] = [Primitive::fromString($type), $type];
        }

        $examples[] = [Primitive::decimal(4, 2), [Schema::ATTR_TYPE => Primitive::TYPE_BYTES, 'foo' => 'bar']];
        $examples[] = [Primitive::date(), [Schema::ATTR_TYPE => Primitive::TYPE_INT, 'foo' => 'bar']];
        $examples[] = [Primitive::timeMillis(), [Schema::ATTR_TYPE => Primitive::TYPE_INT, 'foo' => 'bar']];
        $examples[] = [Primitive::timeMicros(), [Schema::ATTR_TYPE => Primitive::TYPE_INT, 'foo' => 'bar']];
        $examples[] = [Primitive::timestampMillis(), [Schema::ATTR_TYPE => Primitive::TYPE_LONG, 'foo' => 'bar']];
        $examples[] = [Primitive::timestampMicros(), [Schema::ATTR_TYPE => Primitive::TYPE_LONG, 'foo' => 'bar']];

        return $examples;
    }

    public function canonicalExamples(): array
    {
        $examples = [];

        foreach (Primitive::TYPES as $type) {
            $examples[] = [Primitive::fromString($type), [Schema::ATTR_TYPE => $type]];
        }

        $examples[] = [Primitive::decimal(4, 2), [Schema::ATTR_TYPE => Primitive::TYPE_BYTES]];
        $examples[] = [Primitive::date(), [Schema::ATTR_TYPE => Primitive::TYPE_INT]];
        $examples[] = [Primitive::timeMillis(), [Schema::ATTR_TYPE => Primitive::TYPE_INT]];
        $examples[] = [Primitive::timeMicros(), [Schema::ATTR_TYPE => Primitive::TYPE_INT]];
        $examples[] = [Primitive::timestampMillis(), [Schema::ATTR_TYPE => Primitive::TYPE_LONG]];
        $examples[] = [Primitive::timestampMicros(), [Schema::ATTR_TYPE => Primitive::TYPE_LONG]];

        return $examples;
    }
}
