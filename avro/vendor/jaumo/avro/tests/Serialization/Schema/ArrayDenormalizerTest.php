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
use Avro\Model\Schema\Array_;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\ArrayDenormalizer;
use Avro\Serialization\Schema\Denormalizer;
use Avro\Serialization\Schema\DenormalizerAware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ArrayDenormalizerTest extends TestCase
{
    /**
     * @var ArrayDenormalizer
     */
    private $denormalizer;

    /**
     * @var Denormalizer|MockObject
     */
    private $delegated;

    public function setUp(): void
    {
        $this->delegated = $this->createMock(Denormalizer::class);
        $this->denormalizer = new ArrayDenormalizer();
        $this->denormalizer->setDenormalizer($this->delegated);
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Denormalizer::class, $this->denormalizer);
        $this->assertInstanceOf(DenormalizerAware::class, $this->denormalizer);
    }

    public function testSupporting(): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Array_::TYPE, Array_::ATTR_ITEMS => Primitive::TYPE_STRING], Schema::class));
        $this->assertTrue($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Array_::TYPE, Array_::ATTR_ITEMS => Primitive::TYPE_STRING], Array_::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Primitive::TYPE_INT], Array_::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Array_::TYPE, Array_::ATTR_ITEMS => Primitive::TYPE_STRING], Primitive::class));
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizing(): void
    {
        $type = new class() implements Schema {
        };
        $this->delegated
            ->method('denormalize')
            ->with($this->equalTo([Schema::ATTR_TYPE => Primitive::TYPE_STRING]), Schema::class)
            ->willReturn($type);
        $this->assertEquals(
            Array_::of($type),
            $this->denormalizer->denormalize([Schema::ATTR_TYPE => Array_::TYPE, Array_::ATTR_ITEMS => Primitive::TYPE_STRING])
        );
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithExpandedItems(): void
    {
        $type = new class() implements Schema {
        };
        $this->delegated
            ->method('denormalize')
            ->with($this->equalTo([Schema::ATTR_TYPE => Primitive::TYPE_STRING]), Schema::class)
            ->willReturn($type);
        $this->assertEquals(
            Array_::of($type),
            $this->denormalizer->denormalize([Schema::ATTR_TYPE => Array_::TYPE, Array_::ATTR_ITEMS => [Schema::ATTR_TYPE => Primitive::TYPE_STRING]])
        );
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithoutItems(): void
    {
        $this->expectException(DenormalizationError::class);
        $this->denormalizer->denormalize([Schema::ATTR_TYPE => Array_::TYPE]);
    }
}
