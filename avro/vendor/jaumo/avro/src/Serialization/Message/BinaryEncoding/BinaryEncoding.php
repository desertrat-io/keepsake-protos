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
use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\Map;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\Reference;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;

class BinaryEncoding
{
    private const ENCODING_MAP = [
        Primitive::class => PrimitiveEncoding::class,
        Fixed::class => FixedEncoding::class,
        Record::class => RecordEncoding::class,
        Enum::class => EnumEncoding::class,
        Union::class => UnionEncoding::class,
        Array_::class => ArrayEncoding::class,
        Map::class => MapEncoding::class,
    ];

    /**
     * @param Schema $schema
     * @param mixed $value
     * @return string
     * @throws AvroException
     */
    public static function encode(Schema $schema, $value): string
    {
        if ($schema instanceof Reference) {
            $schema = $schema->getSchema();
        }
        /** @var callable $encoder */
        $encoder = [self::resolveEncoding($schema), 'encode'];

        return $encoder($schema, $value);
    }

    /**
     * @param Schema $schema
     * @param ByteReader $reader
     * @return mixed
     * @throws AvroException
     */
    public static function decode(Schema $schema, ByteReader $reader)
    {
        if ($schema instanceof Reference) {
            $schema = $schema->getSchema();
        }

        /** @var callable $decoder */
        $decoder = [self::resolveEncoding($schema), 'decode'];

        return $decoder($schema, $reader);
    }

    /**
     * @param Schema $schema
     * @return string
     * @throws AvroException
     */
    private static function resolveEncoding(Schema $schema): string
    {
        $realType = \get_class($schema);

        if (!isset(self::ENCODING_MAP[$realType])) {
            throw AvroException::unknownType($realType);
        }

        return self::ENCODING_MAP[$realType];
    }
}
