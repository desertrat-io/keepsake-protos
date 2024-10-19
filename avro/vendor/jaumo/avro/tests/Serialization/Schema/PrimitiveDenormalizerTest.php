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

use Avro\AvroException;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\Denormalizer;
use Avro\Serialization\Schema\PrimitiveDenormalizer;
use PHPUnit\Framework\TestCase;

final class PrimitiveDenormalizerTest extends TestCase
{
    /**
     * @var PrimitiveDenormalizer
     */
    private $denormalizer;

    public function setUp(): void
    {
        $this->denormalizer = new PrimitiveDenormalizer();
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Denormalizer::class, $this->denormalizer);
    }

    /**
     * @dataProvider primitiveTypeExamples
     *
     * @param array $data
     */
    public function testSupporting(array $data): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization($data, Schema::class));
        $this->assertTrue($this->denormalizer->supportsDenormalization($data, Primitive::class));
    }

    /**
     * @dataProvider primitiveTypeExamples
     *
     * @param array $data
     * @param Schema $schema
     * @throws AvroException
     */
    public function testDenormalizing(array $data, Schema $schema): void
    {
        $this->assertEquals($schema, $this->denormalizer->denormalize($data));
    }

    /**
     * @throws AvroException
     */
    public function testMissingTypeField(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([]);
    }

    /**
     * @throws AvroException
     */
    public function testUnknownType(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Schema::ATTR_TYPE => 'jaumo']);
    }

    public function primitiveTypeExamples(): array
    {
        return [
            [[Schema::ATTR_TYPE => Primitive::TYPE_NULL], Primitive::null()],
            [[Schema::ATTR_TYPE => Primitive::TYPE_BOOLEAN], Primitive::boolean()],
            [[Schema::ATTR_TYPE => Primitive::TYPE_INT], Primitive::int()],
            [[Schema::ATTR_TYPE => Primitive::TYPE_LONG], Primitive::long()],
            [[Schema::ATTR_TYPE => Primitive::TYPE_FLOAT], Primitive::float()],
            [[Schema::ATTR_TYPE => Primitive::TYPE_DOUBLE], Primitive::double()],
            [[Schema::ATTR_TYPE => Primitive::TYPE_BYTES], Primitive::bytes()],
            [[Schema::ATTR_TYPE => Primitive::TYPE_STRING], Primitive::string()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_DECIMAL, Schema::ATTR_TYPE => Primitive::TYPE_BYTES, Schema::ATTR_LOGICAL_TYPE_DECIMAL_PRECISION => 4, Schema::ATTR_LOGICAL_TYPE_DECIMAL_SCALE => 2], Primitive::decimal(4, 2)],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_DECIMAL, Schema::ATTR_TYPE => Primitive::TYPE_BYTES, Schema::ATTR_LOGICAL_TYPE_DECIMAL_PRECISION => 4], Primitive::decimal(4)],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_DECIMAL, Schema::ATTR_TYPE => Primitive::TYPE_BYTES, Schema::ATTR_LOGICAL_TYPE_DECIMAL_PRECISION => 4, Schema::ATTR_LOGICAL_TYPE_DECIMAL_SCALE => 10], Primitive::bytes()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_DECIMAL, Schema::ATTR_TYPE => Primitive::TYPE_INT, Schema::ATTR_LOGICAL_TYPE_DECIMAL_PRECISION => 4], Primitive::int()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_DATE, Schema::ATTR_TYPE => Primitive::TYPE_INT], Primitive::date()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_DATE, Schema::ATTR_TYPE => Primitive::TYPE_STRING], Primitive::string()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_TIME_MILLIS, Schema::ATTR_TYPE => Primitive::TYPE_INT], Primitive::timeMillis()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_TIME_MILLIS, Schema::ATTR_TYPE => Primitive::TYPE_STRING], Primitive::string()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_TIME_MICROS, Schema::ATTR_TYPE => Primitive::TYPE_INT], Primitive::timeMicros()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_TIME_MICROS, Schema::ATTR_TYPE => Primitive::TYPE_STRING], Primitive::string()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_TIMESTAMP_MILLIS, Schema::ATTR_TYPE => Primitive::TYPE_LONG], Primitive::timestampMillis()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_TIMESTAMP_MILLIS, Schema::ATTR_TYPE => Primitive::TYPE_STRING], Primitive::string()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_TIMESTAMP_MICROS, Schema::ATTR_TYPE => Primitive::TYPE_LONG], Primitive::timestampMicros()],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_TIMESTAMP_MICROS, Schema::ATTR_TYPE => Primitive::TYPE_STRING], Primitive::string()],
        ];
    }
}
