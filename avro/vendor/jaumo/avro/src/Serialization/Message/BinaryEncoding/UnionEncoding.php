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
use Avro\Model\Schema\Union;
use Avro\Validation\Validating;

class UnionEncoding
{
    /**
     * @throws AvroException
     */
    public static function encode(Union $schema, $value): string
    {
        $index = null;
        $realSchema = null;
        foreach ($schema->getTypes() as $index => $type) {
            if (Validating::isValid($value, $type)) {
                $realSchema = $type;
                break;
            }
        }

        if (null === $realSchema) {
            throw new AvroException('Value is not valid against the union type');
        }

        return PrimitiveEncoding::encodeLongOrInt((int) $index) . BinaryEncoding::encode($realSchema, $value);
    }

    /**
     * @throws AvroException
     */
    public static function decode(Union $schema, ByteReader $reader)
    {
        $index = PrimitiveEncoding::decodeLongOrInt($reader);
        $types = $schema->getTypes();

        if (!isset($types[$index])) {
            throw ReadError::unknownUnionIndex($index);
        }

        $expectedType = $types[$index];
        $value = BinaryEncoding::decode($expectedType, $reader);

        return $value;
    }
}
