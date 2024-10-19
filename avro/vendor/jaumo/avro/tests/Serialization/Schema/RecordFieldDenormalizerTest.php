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
use Avro\Model\Schema\Name;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\RecordFieldDefault;
use Avro\Model\Schema\RecordFieldOrder;
use Avro\Model\Schema\Schema;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\Denormalizer;
use Avro\Serialization\Schema\DenormalizerAware;
use Avro\Serialization\Schema\RecordFieldDenormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RecordFieldDenormalizerTest extends TestCase
{
    /**
     * @var RecordFieldDenormalizer
     */
    private $denormalizer;

    /**
     * @var Denormalizer|MockObject
     */
    private $delegated;

    public function setUp(): void
    {
        $this->delegated = $this->createMock(Denormalizer::class);
        $this->denormalizer = new RecordFieldDenormalizer();

        $this->denormalizer->setDenormalizer($this->delegated);
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Denormalizer::class, $this->denormalizer);
        $this->assertInstanceOf(DenormalizerAware::class, $this->denormalizer);
    }

    public function testSupporting(): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization([], RecordField::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([], Schema::class));
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingTypeWithPrimitiveType(): void
    {
        $type = new class() implements Schema {
        };
        $this->delegated
             ->method('denormalize')
             ->with(
                 $this->equalTo([Schema::ATTR_TYPE => Primitive::TYPE_INT]),
                 Schema::class
             )
            ->willReturn($type);

        $data = [RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Primitive::TYPE_INT];
        $schema = RecordField::named(Name::fromValue('foo'), $type);

        $this->assertEquals($schema, $this->denormalizer->denormalize($data));
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingTypeWithSchemaType(): void
    {
        $type = new class() implements Schema {
        };
        $this->delegated
             ->method('denormalize')
             ->with(
                 $this->equalTo([Schema::ATTR_TYPE => Primitive::TYPE_INT]),
                 Schema::class
             )
            ->willReturn($type);

        $data = [RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => [Schema::ATTR_TYPE => Primitive::TYPE_INT]];
        $schema = RecordField::named(Name::fromValue('foo'), $type);

        $this->assertEquals($schema, $this->denormalizer->denormalize($data));
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingRecordFieldTypeWithDoc(): void
    {
        $type = new class() implements Schema {
        };
        $this->delegated
             ->method('denormalize')
             ->with($this->equalTo([Schema::ATTR_TYPE => Primitive::TYPE_INT]), Schema::class)
            ->willReturn($type);

        $data = [RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Primitive::TYPE_INT, 'doc' => 'Some field'];
        $schema = RecordField::named(Name::fromValue('foo'), $type)->withDoc('Some field');

        $this->assertEquals($schema, $this->denormalizer->denormalize($data));
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithValidDefaultValue(): void
    {
        $type = Primitive::null();
        $this->delegated
            ->method('denormalize')
            ->with($this->equalTo([Schema::ATTR_TYPE => Primitive::TYPE_NULL]), Schema::class)
            ->willReturn($type);

        $this->assertEquals(
            RecordField::named(Name::fromValue('foo'), $type)->withDefault(RecordFieldDefault::fromValue(null)),

            $this->denormalizer->denormalize([RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => [Schema::ATTR_TYPE => Primitive::TYPE_NULL], 'default' => null])
        );
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithInvalidDefaultValue(): void
    {
        $this->expectException(DenormalizationError::class);

        $type = Primitive::int();
        $this->delegated
            ->method('denormalize')
            ->with($this->equalTo([Schema::ATTR_TYPE => Primitive::TYPE_INT]), Schema::class)
            ->willReturn($type);

        $this->denormalizer->denormalize([RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => [Schema::ATTR_TYPE => Primitive::TYPE_INT], 'default' => null]);
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithOrder(): void
    {
        $type = new class() implements Schema {
        };
        $this->delegated->method('denormalize')->willReturn($type);
        $this->assertEquals(
            RecordField::named(Name::fromValue('foo'), $type)->withOrder(RecordFieldOrder::fromValue('ascending')),
            $this->denormalizer->denormalize([RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => [Schema::ATTR_TYPE => Primitive::TYPE_INT], 'order' => 'ascending'])
        );
        $this->assertEquals(
            RecordField::named(Name::fromValue('foo'), $type)->withOrder(RecordFieldOrder::fromValue('descending')),
            $this->denormalizer->denormalize([RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => [Schema::ATTR_TYPE => Primitive::TYPE_INT], 'order' => 'descending'])
        );
        $this->assertEquals(
            RecordField::named(Name::fromValue('foo'), $type)->withOrder(RecordFieldOrder::fromValue('ignore')),
            $this->denormalizer->denormalize([RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => [Schema::ATTR_TYPE => Primitive::TYPE_INT], 'order' => 'ignore'])
        );

        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => [Schema::ATTR_TYPE => Primitive::TYPE_INT], 'order' => 'fancy']);
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithAliases(): void
    {
        $type = new class() implements Schema {
        };
        $this->delegated->method('denormalize')->willReturn($type);
        $this->assertEquals(
            RecordField::named(Name::fromValue('foo'), $type)->withAliases(['bar']),
            $this->denormalizer->denormalize([RecordField::ATTR_NAME => 'foo', Schema::ATTR_TYPE => [Schema::ATTR_TYPE => Primitive::TYPE_INT], 'aliases' => ['bar']])
        );
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithoutTypeField(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([]);
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithoutNameField(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Schema::ATTR_TYPE => Primitive::TYPE_INT]);
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizingWithInvalidName(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([RecordField::ATTR_NAME => '123FOO@bar', Schema::ATTR_TYPE => Primitive::TYPE_INT]);
    }
}
