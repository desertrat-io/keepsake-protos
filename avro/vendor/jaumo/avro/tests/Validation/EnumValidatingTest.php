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

use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Schema;
use Avro\Validation\EnumValidating;
use PHPUnit\Framework\TestCase;

final class EnumValidatingTest extends TestCase
{
    public function testEnumSymbol(): void
    {
        $this->assertTrue(
            EnumValidating::isValid(
                'AAA',
                Enum::named(NamespacedName::fromValue('foo'), [
                    Name::fromValue('AAA'),
                    Name::fromValue('BBB'),
                ])
            )
        );
    }

    public function testNonEnumSymbol(): void
    {
        $this->assertFalse(
            EnumValidating::isValid(
                'CCC',
                Enum::named(NamespacedName::fromValue('foo'), [
                    Name::fromValue('AAA'),
                    Name::fromValue('BBB'),
                ])
            )
        );
    }

    public function testNonString(): void
    {
        $this->assertFalse(
            EnumValidating::isValid(
                42,
                Enum::named(NamespacedName::fromValue('foo'), [
                    Name::fromValue('AAA'),
                    Name::fromValue('BBB'),
                ])
            )
        );
    }

    public function testNonEnumSchema(): void
    {
        $this->expectException(\TypeError::class);
        EnumValidating::isValid(
            'AAA',
            new class() implements Schema {
            }
        );
    }
}
