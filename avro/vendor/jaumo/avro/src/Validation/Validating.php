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

namespace Avro\Validation;

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

final class Validating
{
    private const VALIDATING_MAP = [
        Primitive::class => PrimitiveValidating::class,
        Fixed::class => FixedValidating::class,
        Record::class => RecordValidating::class,
        Enum::class => EnumValidating::class,
        Union::class => UnionValidating::class,
        Array_::class => ArrayValidating::class,
        Map::class => MapValidating::class,
        Reference::class => ReferenceValidating::class,
    ];

    public static function isValid($value, Schema $schema): bool
    {
        $realType = \get_class($schema);
        if (!isset(self::VALIDATING_MAP[$realType])) {
            throw AvroException::unknownType($realType);
        }

        /** @var callable $validator */
        $validator = [self::VALIDATING_MAP[$realType], 'isValid'];

        return $validator($value, $schema);
    }
}
