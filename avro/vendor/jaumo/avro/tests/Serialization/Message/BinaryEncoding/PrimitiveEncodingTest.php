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

use Avro\Model\Schema\Primitive;
use Avro\Serialization\Message\BinaryEncoding\PrimitiveEncoding;
use Avro\Serialization\Message\BinaryEncoding\ReadError;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use PHPUnit\Framework\TestCase;

class PrimitiveEncodingTest extends TestCase
{
    /**
     * @dataProvider primitiveData
     *
     * @throws ReadError
     */
    public function testDecode(string $type, $expected, string $bytes): void
    {
        $reader = new StringByteReader($bytes);
        $decoded = PrimitiveEncoding::decode(Primitive::fromString($type), $reader);

        if (\is_float($expected) && \is_nan($expected)) {
            $this->assertNan($decoded);
        } else {
            $this->assertEquals($expected, $decoded, "Decode $type $expected");
        }
    }

    /**
     * @dataProvider primitiveData
     */
    public function testEncode(string $type, $value, string $expected): void
    {
        $this->assertEquals($expected, PrimitiveEncoding::encode(Primitive::fromString($type), $value));
    }

    public static function primitiveData(): array
    {
        // The following data is generated with the tool in `tests/binary-encoding-generator`
        // You can run it with `cargo run` in the tool directory
        return [
            [Primitive::TYPE_BOOLEAN, false, \base64_decode('AA==', true)],
            [Primitive::TYPE_BOOLEAN, true, \base64_decode('AQ==', true)],
            [Primitive::TYPE_BYTES, '', \base64_decode('AA==', true)],
            [Primitive::TYPE_BYTES, "\00\01\02\03\04\05\06\07abcdeABCDE\n\t\r", \base64_decode('KgABAgMEBQYHYWJjZGVBQkNERQoJDQ==', true)],
            [Primitive::TYPE_DOUBLE, -1.0, \base64_decode('AAAAAAAA8L8=', true)],
            [Primitive::TYPE_DOUBLE, 0.0, \base64_decode('AAAAAAAAAAA=', true)],
            [Primitive::TYPE_DOUBLE, 1.0, \base64_decode('AAAAAAAA8D8=', true)],
            [Primitive::TYPE_DOUBLE, 100000.123123123123, \base64_decode('s/NP+AFq+EA=', true)],
            [Primitive::TYPE_DOUBLE, 9.1345596313477, \base64_decode('GQAAAOVEIkA=', true)],
            [Primitive::TYPE_DOUBLE, INF, \base64_decode('AAAAAAAA8H8=', true)],
            [Primitive::TYPE_DOUBLE, NAN, \base64_decode('AAAAAAAA+H8=', true)],
            [Primitive::TYPE_FLOAT, -1.0, \base64_decode('AACAvw==', true)],
            [Primitive::TYPE_FLOAT, 0.0, \base64_decode('AAAAAA==', true)],
            [Primitive::TYPE_FLOAT, 1.0, \base64_decode('AACAPw==', true)],
            [Primitive::TYPE_FLOAT, 100000.125, \base64_decode('EFDDRw==', true)],
            [Primitive::TYPE_FLOAT, 9.134559631347656, \base64_decode('KCcSQQ==', true)],
            [Primitive::TYPE_FLOAT, INF, \base64_decode('AACAfw==', true)],
            [Primitive::TYPE_FLOAT, NAN, \base64_decode('AADAfw==', true)],
            [Primitive::TYPE_INT, -1, \base64_decode('AQ==', true)],
            [Primitive::TYPE_INT, -2147483648, \base64_decode('/////w8=', true)],
            [Primitive::TYPE_INT, 0, \base64_decode('AA==', true)],
            [Primitive::TYPE_INT, 1, \base64_decode('Ag==', true)],
            [Primitive::TYPE_INT, 2147483647, \base64_decode('/v///w8=', true)],
            [Primitive::TYPE_LONG, -1, \base64_decode('AQ==', true)],
            [Primitive::TYPE_LONG, 0, \base64_decode('AA==', true)],
            [Primitive::TYPE_LONG, 1, \base64_decode('Ag==', true)],
            [Primitive::TYPE_LONG, PHP_INT_MAX, \base64_decode('/v//////////AQ==', true)],
            [Primitive::TYPE_LONG, PHP_INT_MIN, \base64_decode('////////////AQ==', true)],
            [Primitive::TYPE_NULL, null, \base64_decode('', true)],
            [Primitive::TYPE_STRING, '', \base64_decode('AA==', true)],
            [Primitive::TYPE_STRING, 'abcdäöü😀_👊', \base64_decode('JmFiY2TDpMO2w7zwn5iAX/CfkYo=', true)],
        ];
    }
}
