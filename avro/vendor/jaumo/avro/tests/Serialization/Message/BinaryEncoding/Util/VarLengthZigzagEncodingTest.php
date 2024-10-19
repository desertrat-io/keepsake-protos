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

namespace AvroTest\Serialization\Message\BinaryEncoding\Util;

use Avro\Serialization\Message\BinaryEncoding\ReadError;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use Avro\Serialization\Message\BinaryEncoding\Util\VarLengthZigZagEncoding;
use PHPUnit\Framework\TestCase;

class VarLengthZigzagEncodingTest extends TestCase
{
    /**
     * @dataProvider zigzagData
     *
     * @param string $bytes
     * @param int $expected
     * @throws ReadError
     */
    public function testDecode(string $bytes, int $expected): void
    {
        $reader = new StringByteReader($bytes);
        $this->assertEquals($expected, VarLengthZigZagEncoding::decode($reader));
    }

    /**
     * @dataProvider zigzagData
     *
     * @param string $expected
     * @param int $value
     */
    public function testEncode(string $expected, int $value): void
    {
        $this->assertEquals($expected, VarLengthZigZagEncoding::encode($value));
    }

    public static function zigzagData(): array
    {
        return [
            ["\x00", 0],
            ["\x01", -1],
            ["\x02", 1],
            ["\x03", -2],
            ["\x04", 2],
            ["\x7f", -64],
            ["\x80\x01", 64],
            ["\xa0\x9c\x1", 10000],
            ["\xfe\xff\xff\xff\xf", 2147483647],
            ["\xda\x94\x87\xee\xdf\x5", 98765432109],
            ["\xff\xff\xff\xff\xf", -2147483648],
            ["\xd9\x94\x87\xee\xdf\x5", -98765432109],
            [\base64_decode('/v//////////AQ==', true), 9223372036854775807],
            [\base64_decode('////////////AQ==', true), (int) -9223372036854775808],
        ];
    }
}
