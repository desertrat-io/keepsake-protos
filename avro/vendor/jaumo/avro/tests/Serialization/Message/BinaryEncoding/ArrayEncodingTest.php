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
use Avro\Model\Schema\Array_;
use Avro\Model\Schema\Primitive;
use Avro\Serialization\Message\BinaryEncoding\ArrayEncoding;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use PHPUnit\Framework\TestCase;

class ArrayEncodingTest extends TestCase
{
    /**
     * @dataProvider arrayData
     *
     * @throws AvroException
     */
    public function testDecode(string $message, Array_ $schema, array $expected): void
    {
        $decoded = ArrayEncoding::decode($schema, new StringByteReader($message));

        $this->assertEquals($expected, $decoded);
    }

    /**
     * @dataProvider arrayData
     */
    public function testEncode(string $expected, Array_ $schema, array $value): void
    {
        $this->assertEquals($expected, ArrayEncoding::encode($schema, $value));
    }

    public static function arrayData(): array
    {
        $schema = Array_::of(Primitive::long());

        return [
            ["\x3\x4\x6\x36\00", Array_::of(Primitive::long()), [3, 27]],
        ];
    }
}
