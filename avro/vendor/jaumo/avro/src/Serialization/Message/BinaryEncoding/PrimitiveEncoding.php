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

namespace Avro\Serialization\Message\BinaryEncoding;

use Avro\Model\Schema\Primitive;
use Avro\Serialization\Message\BinaryEncoding\Util\VarLengthZigZagEncoding;
use LogicException;
use RuntimeException;

class PrimitiveEncoding
{
    /**
     * Encode a Primitive according to its type
     *
     */
    public static function encode(Primitive $schema, $value): string
    {
        $type = $schema->getType();

        switch ($type) {
            case Primitive::TYPE_NULL:
                return '';

            case Primitive::TYPE_BOOLEAN:
                return self::encodeBoolean($value);

            case Primitive::TYPE_STRING:
                return self::encodeString($value);

            case Primitive::TYPE_BYTES:
                return self::encodeBytes($value);

            case Primitive::TYPE_INT:
            case Primitive::TYPE_LONG:
                return self::encodeLongOrInt($value);

            case Primitive::TYPE_FLOAT:
                return self::encodeFloat($value);

            case Primitive::TYPE_DOUBLE:
                return self::encodeDouble($value);

            default:
                throw new LogicException(\sprintf('Unknown primitive type "%s"', $type));
        }
    }

    /**
     * Decode a Primitive according to its type
     *
     * @throws ReadError
     */
    public static function decode(Primitive $primitive, ByteReader $byteReader)
    {
        switch ($primitive->getType()) {
            case Primitive::TYPE_NULL:
                return null;

            case Primitive::TYPE_BOOLEAN:
                return self::decodeBoolean($byteReader);

            case Primitive::TYPE_STRING:
                return self::decodeString($byteReader);

            case Primitive::TYPE_BYTES:
                return self::decodeBytes($byteReader);

            case Primitive::TYPE_INT:
            case Primitive::TYPE_LONG:
                return self::decodeLongOrInt($byteReader);

            case Primitive::TYPE_FLOAT:
                return self::decodeFloat($byteReader);

            case Primitive::TYPE_DOUBLE:
                return self::decodeDouble($byteReader);

            default:
                throw new LogicException(\sprintf('Unknown primitive type "%s"', $primitive->getType()));
        }
    }

    /**
     * Encode a string which is a UTF-8 sequence prefixed with its byte-length
     *
     * Since PHP does not differentiate between byte-strings and UTF-8 strings
     * this is the same as a number of bytes.
     *
     */
    public static function encodeString(string $value): string
    {
        return self::encodeBytes($value);
    }

    /**
     * Decode a string which is a UTF-8 sequence prefixed with its byte-length
     *
     * Since PHP does not differentiate between byte-strings and UTF-8 strings
     * this is the same as a number of bytes.
     *
     * @throws ReadError
     */
    public static function decodeString(ByteReader $byteReader): string
    {
        return self::decodeBytes($byteReader);
    }

    /**
     * Encode a byte-sequence which is the raw bytes prefixed with the number of bytes
     *
     */
    public static function encodeBytes(string $bytes): string
    {
        return self::encodeLongOrInt(\strlen($bytes)) . $bytes;
    }

    /**
     * Decode a byte-sequence which is the raw bytes prefixed with the number of bytes
     *
     * @throws ReadError
     */
    public static function decodeBytes(ByteReader $byteReader): string
    {
        $numBytes = self::decodeLongOrInt($byteReader);

        return $byteReader->read($numBytes);
    }

    /**
     * Encode a long or int which is encoded with variable length zig-zag-encoding
     *
     */
    public static function encodeLongOrInt(int $value): string
    {
        return VarLengthZigZagEncoding::encode($value);
    }

    /**
     * Decode a long or int which is encoded with variable length zig-zag-encoding
     *
     * @throws ReadError
     */
    public static function decodeLongOrInt(ByteReader $byteReader): int
    {
        return VarLengthZigZagEncoding::decode($byteReader);
    }

    /**
     * Encode a float (IEEE 754 single precision)
     *
     * This method assumes PHP is running on the IEEE 754 floating-point
     * implementation, which should be almost always true. If it is not,
     * you already know.
     *
     */
    public static function encodeFloat(float $value): string
    {
        return \pack('f', $value);
    }

    /**
     * Decode a float (IEEE 754 single precision)
     *
     * This method assumes PHP is running on the IEEE 754 floating-point
     * implementation, which should be almost always true. If it is not,
     * you already know.
     *
     * @throws ReadError
     */
    public static function decodeFloat(ByteReader $byteReader): float
    {
        $bytes = $byteReader->read(4);
        $unpacked = \unpack('f', $bytes);

        if (false === $unpacked) {
            throw new RuntimeException('Failed to unpack float');
        }

        return (float) $unpacked[1];
    }

    /**
     * Encode a float (IEEE 754 double precision)
     *
     * This method assumes PHP is running on the IEEE 754 floating-point
     * implementation, which should be almost always true. If it is not,
     * you already know.
     *
     */
    public static function encodeDouble(float $value): string
    {
        return \pack('d', $value);
    }

    /**
     * Decode a float (IEEE 754 double precision)
     *
     * This method assumes PHP is running on the IEEE 754 floating-point
     * implementation, which should be almost always true. If it is not,
     * you already know.
     *
     * @throws ReadError
     */
    public static function decodeDouble(ByteReader $byteReader): float
    {
        $bytes = $byteReader->read(8);

        $unpacked = \unpack('d', $bytes);

        if (false === $unpacked) {
            throw new RuntimeException('Failed to unpack double');
        }

        return (float) $unpacked[1];
    }

    public static function encodeBoolean(bool $value): string
    {
        return $value ? "\1" : "\0";
    }

    /**
     * @throws ReadError
     */
    public static function decodeBoolean(ByteReader $byteReader): bool
    {
        switch ($byteReader->read(1)) {
            case "\x0":
                return false;
            case "\x1":
                return true;
            default:
                throw new ReadError('Invalid byte for boolean type');
        }
    }
}
