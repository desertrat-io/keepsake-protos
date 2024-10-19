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
use Avro\Model\Schema\Map;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Message\BinaryEncoding\Util\BlockEncoding;

class MapEncoding
{
    /**
     * @throws AvroException
     */
    public static function encode(Map $schema, $value): string
    {
        $keyValueSchema = self::makeKeyValueSchema($schema->getValues());
        $keyValuePairs = [];

        foreach ($value as $key => $item) {
            $keyValuePairs[] = BinaryEncoding::encode(
                $keyValueSchema,
                [
                    'key' => $key,
                    'value' => $item,
                ]
            );
        }

        // Just write the map in one single block
        return BlockEncoding::encode($keyValuePairs);
    }

    /**
     * @throws AvroException
     */
    public static function decode(Map $schema, ByteReader $reader): array
    {
        $keyValuePairs = BlockEncoding::decode(self::makeKeyValueSchema($schema->getValues()), $reader);

        // Convert our intermediary records into a pure array
        $items = [];
        foreach ($keyValuePairs as $keyValuePair) {
            $items[$keyValuePair['key']] = $keyValuePair['value'];
        }

        return $items;
    }

    private static function makeKeyValueSchema(Schema $valueSchema): Record
    {
        // A map is a block of key-value pairs which can be described as a record containing
        // a string, the key, and any type as the value
        return Record::named(NamespacedName::fromValue('entry'))
            ->withAddedField(RecordField::named(Name::fromValue('key'), Primitive::string()))
            ->withAddedField(RecordField::named(Name::fromValue('value'), $valueSchema));
    }
}
