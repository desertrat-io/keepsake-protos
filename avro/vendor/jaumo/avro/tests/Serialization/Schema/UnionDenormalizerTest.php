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
use Avro\Model\Schema\Union;
use Avro\Serialization\Schema\Denormalizer;
use Avro\Serialization\Schema\DenormalizerAware;
use Avro\Serialization\Schema\UnionDenormalizer;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UnionDenormalizerTest extends TestCase
{
    /**
     * @var UnionDenormalizer
     */
    private $denormalizer;

    /**
     * @var Denormalizer|MockObject
     */
    private $delegated;

    public function setUp(): void
    {
        $this->delegated = $this->createMock(Denormalizer::class);
        $this->denormalizer = new UnionDenormalizer();
        $this->denormalizer->setDenormalizer($this->delegated);
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Denormalizer::class, $this->denormalizer);
        $this->assertInstanceOf(DenormalizerAware::class, $this->denormalizer);
    }

    public function testSupporting(): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization([Primitive::TYPE_NULL, Primitive::TYPE_STRING], Schema::class));
        $this->assertTrue($this->denormalizer->supportsDenormalization([Primitive::TYPE_NULL, Primitive::TYPE_STRING], Union::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Primitive::TYPE_INT], Union::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([4 => 'foo', 8 => 'bar'], Union::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Primitive::TYPE_NULL, Primitive::TYPE_STRING], Primitive::class));
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizing(): void
    {
        $null = new class() implements Schema {
        };
        $string = new class() implements Schema {
        };
        $this->delegated
            ->method('denormalize')
            ->will($this->returnCallback(function (array $data) use ($null, $string) {
                switch ($data[Schema::ATTR_TYPE]) {
                    case Primitive::TYPE_NULL:
                        return $null;

                    case Primitive::TYPE_STRING:
                        return $string;

                    default:
                        throw new AssertionFailedError('This should never be reached');
                }
            }));

        $this->assertEquals(
            Union::of([$null, $string]),
            $this->denormalizer->denormalize([Primitive::TYPE_NULL, [Schema::ATTR_TYPE => Primitive::TYPE_STRING]])
        );
    }
}
