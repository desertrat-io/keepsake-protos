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
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Union;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use Avro\Serialization\Message\BinaryEncoding\UnionEncoding;
use PHPUnit\Framework\TestCase;

class UnionEncodingTest extends TestCase
{
    /**
     * @dataProvider unionData
     *
     * @throws AvroException
     */
    public function testDecode(string $data, Union $schema, $expected): void
    {
        $decoded = UnionEncoding::decode($schema, new StringByteReader($data));

        $this->assertEquals($expected, $decoded);
    }

    /**
     * @dataProvider unionData
     *
     * @throws AvroException
     */
    public function testEncode(string $expected, Union $schema, $message): void
    {
        $this->assertEquals($expected, UnionEncoding::encode($schema, $message));
    }

    public static function unionData(): array
    {
        $schema = Union::of([Primitive::null(), Primitive::string()]);

        return [
            ["\x0", $schema, null],
            ["\x2\x2\x61", $schema, 'a'],
        ];
    }
}
