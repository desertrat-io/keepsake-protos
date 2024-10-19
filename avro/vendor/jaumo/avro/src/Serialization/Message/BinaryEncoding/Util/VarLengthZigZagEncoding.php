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

namespace Avro\Serialization\Message\BinaryEncoding\Util;

use Avro\Serialization\Message\BinaryEncoding\ByteReader;
use Avro\Serialization\Message\BinaryEncoding\ReadError;

/**
 * ZigZagEncoder encodes or decoded numbers per the variable-length zig-zag encoding
 */
class VarLengthZigZagEncoding
{
    private const PHP_INT_BIT_COUNT = PHP_INT_SIZE * 8;

    private const MASK_7_BITS = 0b01111111;

    private const MASK_8TH_BIT = 0b10000000;

    private const MASK_HIGHEST_BIT = 1 << (self::PHP_INT_BIT_COUNT - 1);

    private const MASK_ALL_BUT_HIGHEST_7_BITS = ~(self::MASK_7_BITS << (self::PHP_INT_BIT_COUNT - 7));

    /**
     * Encode a number from the provided byte-reader
     *
     * @param int $value
     * @return string
     */
    public static function encode(int $value): string
    {
        $result = '';

        // Apply zig-zag encoding
        $value = ($value << 1) ^ ($value >> (self::PHP_INT_BIT_COUNT - 1));

        // Loop until there are not more then 7 bits of info left
        while (0 !== ($value & ~self::MASK_7_BITS)) {
            // We need the last 7 bits of the value and need to set the highest-order-bit (0x80)
            // to show that this is not the last byte
            $result .= \chr(($value & self::MASK_7_BITS) | self::MASK_8TH_BIT);

            // We processed 7bits of information, so we shift input 7bits to the right
            $value >>= 7;

            // PHP replicates the sign-bit while right-shifting, which we do not need
            // Therefore we set the highest 7 bits to 0
            $value &= self::MASK_ALL_BUT_HIGHEST_7_BITS;
        }

        // The lowest-value byte is added without the highest-order bit set to 1
        $result .= \chr($value);

        return $result;
    }

    /**
     * Decode a number from the provided byte-reader
     *
     * @param ByteReader $byteReader
     * @return int
     * @throws ReadError
     */
    public static function decode(ByteReader $byteReader): int
    {
        $byteCount = 0;
        $value = 0;

        // Read the right amount of bytes (high-order-bit denotes an additional byte)
        do {
            $byte = \ord($byteReader->read(1));
            $value |= ($byte & self::MASK_7_BITS) << ($byteCount * 7);
            $byteCount++;
        } while (0 !== ($byte & self::MASK_8TH_BIT));

        // Apply zig-zag decoding
        return ($value >> 1 & ~self::MASK_HIGHEST_BIT) ^ -($value & 1);
    }
}
