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

namespace AvroTest\Serialization\Message\BinaryEncoding;

use Avro\AvroException;
use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Serialization\Message\BinaryEncoding\EnumEncoding;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use PHPUnit\Framework\TestCase;

class EnumEncodingTest extends TestCase
{
    /**
     * @dataProvider enumData
     *
     * @throws AvroException
     */
    public function testDecode(string $message, Enum $schema, $expected): void
    {
        $decoded = EnumEncoding::decode($schema, new StringByteReader($message));

        $this->assertEquals($expected, $decoded);
    }

    /**
     * @dataProvider enumData
     *
     * @throws AvroException
     */
    public function testEncode(string $expected, Enum $schema, string $value): void
    {
        $this->assertEquals($expected, EnumEncoding::encode($schema, $value));
    }

    public static function enumData(): array
    {
        $a = Name::fromValue('A');
        $b = Name::fromValue('B');
        $c = Name::fromValue('C');
        $d = Name::fromValue('D');

        $schema = Enum::named(NamespacedName::fromValue('test'), [$a, $b, $c, $d]);

        return [
            ["\0", $schema, 'A'],
            ["\2", $schema, 'B'],
            ["\4", $schema, 'C'],
            ["\6", $schema, 'D'],
        ];
    }
}
