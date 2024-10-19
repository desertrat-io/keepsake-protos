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

use Avro\AvroException;
use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\NamespacedName;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use Avro\Serialization\Message\BinaryEncoding\Util\BlockEncoding;
use PHPUnit\Framework\TestCase;

class BlockEncodingTest extends TestCase
{
    private const DATA = ['A', 'B', 'C', 'D', 'E'];

    private const DATA_IN_SINGLE_BLOCK = "\x9\x0aABCDE\0";

    private const DATA_IN_BLOCK_SIZE_2 = "\x03\x04AB\x03\x04CD\x1\x02E\0";

    public function testEncode(): void
    {
        // Assert block correctness
        $encoded = BlockEncoding::encode(self::DATA);
        $this->assertEquals(self::DATA_IN_SINGLE_BLOCK, $encoded);

        // Assert that by default only a single block is written
        $this->assertEquals(self::DATA_IN_SINGLE_BLOCK, BlockEncoding::encode(self::DATA, 5));
        $this->assertEquals(self::DATA_IN_SINGLE_BLOCK, BlockEncoding::encode(self::DATA, 100));

        // Test with multiple blocks
        $encodedMultiBlock = BlockEncoding::encode(self::DATA, 2);
        $this->assertEquals(self::DATA_IN_BLOCK_SIZE_2, $encodedMultiBlock);

        // Test with empty data with and without explicit block size
        $emptyData = BlockEncoding::encode([], 2);
        $this->assertEquals("\0", $emptyData);
        $emptyData = BlockEncoding::encode([]);
        $this->assertEquals("\0", $emptyData);
    }

    /**
     * @throws AvroException
     */
    public function testDecode(): void
    {
        $singleByteFixedSchema = Fixed::named(NamespacedName::fromValue('test'), 1);

        $this->assertEquals(self::DATA, BlockEncoding::decode(
            $singleByteFixedSchema,
            new StringByteReader(self::DATA_IN_SINGLE_BLOCK)
        ));

        $this->assertEquals(self::DATA, BlockEncoding::decode(
            $singleByteFixedSchema,
            new StringByteReader(self::DATA_IN_BLOCK_SIZE_2)
        ));
    }
}
