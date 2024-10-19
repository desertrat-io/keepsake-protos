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

namespace AvroTest\Validation;

use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\RecordFieldDefault;
use Avro\Model\Schema\Schema;
use Avro\Validation\RecordValidating;
use PHPUnit\Framework\TestCase;

final class RecordValidatingTest extends TestCase
{
    public function testValidArrayRecord(): void
    {
        $this->assertTrue(
            RecordValidating::isValid(
                ['foo' => 'bar'],
                Record::named(NamespacedName::fromValue('Message'))
                    ->withAddedField(RecordField::named(Name::fromValue('foo'), Primitive::string()))
            )
        );
    }

    public function testDefault(): void
    {
        $this->assertTrue(
            RecordValidating::isValid(
                [],
                Record::named(NamespacedName::fromValue('Message'))
                    ->withAddedField(
                        RecordField::named(Name::fromValue('foo'), Primitive::int())
                            ->withDefault(RecordFieldDefault::fromValue(42))
                    )
            )
        );
    }

    public function testInvalidArrayRecord(): void
    {
        $this->assertFalse(
            RecordValidating::isValid(
                ['foo' => 'bar'],
                Record::named(NamespacedName::fromValue('Message'))
                    ->withAddedField(RecordField::named(Name::fromValue('foo'), Primitive::int()))
            )
        );
    }

    public function testNonArrayRecord(): void
    {
        $this->assertFalse(
            RecordValidating::isValid(
                'bar',
                Record::named(NamespacedName::fromValue('Message'))
                    ->withAddedField(RecordField::named(Name::fromValue('foo'), Primitive::string()))
            )
        );
    }

    public function testInvalidMissingKey(): void
    {
        $this->assertFalse(
            RecordValidating::isValid(
                [],
                Record::named(NamespacedName::fromValue('Message'))
                    ->withAddedField(RecordField::named(Name::fromValue('foo'), Primitive::int()))
            )
        );
    }

    public function testNonRecordSchema(): void
    {
        $this->expectException(\TypeError::class);
        RecordValidating::isValid(
            ['foo' => 'bar'],
            new class() implements Schema {
            }
        );
    }
}
