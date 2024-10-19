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

use Avro\Model\Schema\Enum;
use Avro\Model\Schema\InvalidSchemaException;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Schema;
use PHPUnit\Framework\TestCase;

final class EnumTest extends TestCase
{
    public function testIsASchema(): void
    {
        $this->assertInstanceOf(Schema::class, $this->createEnum());
    }

    private function createEnum(array $symbols = []): Enum
    {
        return Enum::named(
            NamespacedName::fromValue('foo'),
            \array_map(function (string $symbol): Name {
                return Name::fromValue($symbol);
            }, $symbols)
        );
    }

    public function testHasSymbols(): void
    {
        $this->assertEquals(
            [Name::fromValue('AAA'), Name::fromValue('BBB')],
            $this->createEnum(['AAA', 'BBB'])->getSymbols()
        );
    }

    public function testDuplicateSymbols(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Enum symbols must be unique, duplicate found: "AAA, AAA"');

        $this->createEnum(['AAA', 'AAA']);
    }

    public function testListOfSymbols(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Enum symbols is not a list');

        Enum::named(NamespacedName::fromValue('foo'), [1 => Name::fromValue('AAA'), 3 => Name::fromValue('BBB')]);
    }

    public function testInvalidSymbol(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Expected symbols to be an array of "Avro\Model\Name", got "string" at position 0');

        Enum::named(NamespacedName::fromValue('foo'), ['007']);
    }

    public function testExistingSymbolPosition(): void
    {
        $enum = $this->createEnum(['AAA', 'BBB']);

        $this->assertSame(0, $enum->getPosition(Name::fromValue('AAA')));
        $this->assertSame(1, $enum->getPosition(Name::fromValue('BBB')));
    }

    public function testUnexistingSymbolPosition(): void
    {
        $enum = $this->createEnum(['AAA', 'BBB']);

        $this->expectException(\InvalidArgumentException::class);

        $enum->getPosition(Name::fromValue('CCC'));
    }
}
