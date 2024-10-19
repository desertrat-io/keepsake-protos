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

use Avro\AvroException;
use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Name;
use Exception;

class EnumEncoding
{
    /**
     * @param Enum $schema
     * @param string $value
     * @return string
     * @throws AvroException
     */
    public static function encode(Enum $schema, string $value): string
    {
        try {
            $index = $schema->getPosition(Name::fromValue($value));
        } catch (Exception $e) {
            throw new AvroException(\sprintf(
                'Failed to lookup enum index for "%s" on enum "%s"',
                $value,
                $schema->getFullName())
            );
        }

        return PrimitiveEncoding::encodeLongOrInt($index);
    }

    /**
     * @param Enum $schema
     * @param ByteReader $reader
     * @return string
     * @throws ReadError
     */
    public static function decode(Enum $schema, ByteReader $reader): string
    {
        $position = PrimitiveEncoding::decodeLongOrInt($reader);

        return $schema->atPosition($position)->getValue();
    }
}
