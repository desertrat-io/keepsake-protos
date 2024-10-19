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
use Avro\Model\Schema\Array_;
use Avro\Serialization\Message\BinaryEncoding\Util\BlockEncoding;

class ArrayEncoding
{
    public static function encode(Array_ $schema, array $values): string
    {
        $itemSchema = $schema->getItems();
        $encodedValues = \array_map(function ($item) use ($itemSchema) {
            return BinaryEncoding::encode($itemSchema, $item);
        }, $values);

        // Just write the array in one single block
        return BlockEncoding::encode($encodedValues);
    }

    /**
     * @throws AvroException
     */
    public static function decode(Array_ $schema, ByteReader $reader): array
    {
        return BlockEncoding::decode($schema->getItems(), $reader);
    }
}
