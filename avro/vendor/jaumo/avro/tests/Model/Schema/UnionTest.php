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

use Avro\Model\Schema\Array_;
use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\InvalidSchemaException;
use Avro\Model\Schema\Map;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;
use PHPUnit\Framework\TestCase;

class UnionTest extends TestCase
{
    public function testIsASchema(): void
    {
        $this->assertInstanceOf(Schema::class, Union::of([
            Primitive::null(),
            Primitive::string(),
        ]));
    }

    public function testHasTypes(): void
    {
        $union = Union::of([
            Primitive::null(),
            Primitive::string(),
        ]);
        $this->assertEquals(
            [
                Primitive::null(),
                Primitive::string(),
            ],
            $union->getTypes()
        );
    }

    public function testNonSchemaTypeMember(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Expected types to be an array of "Avro\Model\Schema", got "string" at position 0');

        Union::of(['foo']);
    }

    public function testUnionTypeMember(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Unions may not immediately contain other unions');

        Union::of([
            Union::of([Primitive::string()]),
        ]);
    }

    /**
     * @dataProvider memberExamples
     *
     * @param array $members
     * @param bool $ok
     */
    public function testMemberCombinations(array $members, bool $ok): void
    {
        if (!$ok) {
            $this->expectException(InvalidSchemaException::class);
            $this->expectExceptionMessage('Unions may not contain more than one schema with the same type');
        }

        $this->assertEquals($members, Union::of($members)->getTypes());
    }

    public function memberExamples(): array
    {
        $string = Primitive::string();
        $integer = Primitive::int();
        $enum = Enum::named(NamespacedName::fromValue('Foo'), [Name::fromValue('AAA'), Name::fromValue('BBB')]);

        return [
            [[$string, $integer], true],
            [[$string, $string], false],
            [[Array_::of($string), Array_::of($integer)], false],
            [[Map::to($string), Map::to($integer)], false],
            [[Record::named(NamespacedName::fromValue('Foo')), Record::named(NamespacedName::fromValue('Bar'))], true],
            [[Fixed::named(NamespacedName::fromValue('Foo'), 16), Fixed::named(NamespacedName::fromValue('Bar'), 25)], true],
            [[$enum, $enum], true],
        ];
    }
}
