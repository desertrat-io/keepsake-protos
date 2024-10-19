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
use Avro\Model\Schema\Map;
use Avro\Model\Schema\Primitive;
use Avro\Serialization\Message\BinaryEncoding\MapEncoding;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use PHPUnit\Framework\TestCase;

class MapEncodingTest extends TestCase
{
    /**
     * @dataProvider mapData
     *
     * @throws AvroException
     */
    public function testDecode(string $data, Map $schema, $expected): void
    {
        $decoded = MapEncoding::decode($schema, new StringByteReader($data));

        $this->assertEquals($expected, $decoded);
    }

    /**
     * @dataProvider mapData
     *
     * @throws AvroException
     */
    public function testEncode(string $expected, Map $schema, $message): void
    {
        $this->assertEquals($expected, MapEncoding::encode($schema, $message));
    }

    public static function mapData(): array
    {
        return [
            [
                //blk info| key-value-pair   | key-value-pair    | empty-block
                //-2  20  3  f   o   o   3   3   b   a   r   27  0
                "\x03\x14\x6\x66\x6f\x6f\x06\x06\x62\x61\x72\x36\x00",
                Map::to(Primitive::long()),
                ['foo' => 3, 'bar' => 27],
            ],
        ];
    }
}
