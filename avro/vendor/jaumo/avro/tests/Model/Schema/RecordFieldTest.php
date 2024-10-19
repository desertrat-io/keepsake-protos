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

namespace AvroTest\Model\Schema;

use Avro\Model\Schema\InvalidSchemaException;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\RecordFieldDefault;
use Avro\Model\Schema\Union;
use PHPUnit\Framework\TestCase;

final class RecordFieldTest extends TestCase
{
    public function testWithValidDefault(): void
    {
        $this->assertEquals(
            RecordFieldDefault::fromValue('foo'),
            (RecordField::named(Name::fromValue('field'), Primitive::string()))
                ->withDefault(RecordFieldDefault::fromValue('foo'))
                ->getDefault()
        );
    }

    public function testWithInvalidDefault(): void
    {
        $this->expectException(InvalidSchemaException::class);

        (RecordField::named(Name::fromValue('field'), Primitive::string()))
            ->withDefault(RecordFieldDefault::fromValue(42));
    }

    public function testWithValidDefaultAndAnUnionType()
    {
        $this->assertEquals(
            RecordFieldDefault::fromValue('foo'),
            (RecordField::named(Name::fromValue('field'), Union::of([Primitive::string(), Primitive::int()])))
                ->withDefault(
                    RecordFieldDefault::fromValue('foo')
                )
                ->getDefault()
        );
    }

    public function testWithInvalidDefaultAndAnUnionType()
    {
        $this->expectException(InvalidSchemaException::class);

        (RecordField::named(Name::fromValue('field'), Union::of([Primitive::string(), Primitive::int()])))
            ->withDefault(RecordFieldDefault::fromValue(42))
            ->getDefault();
    }

    public function testWithDefaultWithEmptyUnionType()
    {
        $this->assertEquals(
            RecordFieldDefault::fromValue('foo'),
            (RecordField::named(Name::fromValue('field'), Union::of([])))
                ->withDefault(
                    RecordFieldDefault::fromValue('foo')
                )
                ->getDefault()
        );
    }
}
