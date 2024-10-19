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

use Avro\Model\Schema\Primitive;

final class PrimitiveValidating
{
    public const MIN_INT_VALUE = (1 << 31) * -1;

    public const MAX_INT_VALUE = (1 << 31) - 1;

    public const MIN_LONG_VALUE = PHP_INT_MIN;

    public const MAX_LONG_VALUE = PHP_INT_MAX;

    public static function isValid($value, Primitive $schema): bool
    {
        switch ($schema->getType()) {
            case Primitive::TYPE_NULL:
                return null === $value;

            case Primitive::TYPE_BOOLEAN:
                return \is_bool($value);

            case Primitive::TYPE_INT:
                return \is_int($value)
                    && $value >= self::MIN_INT_VALUE
                    && $value <= self::MAX_INT_VALUE;

            case Primitive::TYPE_LONG:
                return \is_int($value)
                    && $value >= self::MIN_LONG_VALUE
                    && $value <= self::MAX_LONG_VALUE;

            case Primitive::TYPE_FLOAT:
            case Primitive::TYPE_DOUBLE:
                return \is_int($value) || \is_float($value);

            case Primitive::TYPE_STRING:
            case Primitive::TYPE_BYTES:
                return \is_string($value);

            default:
                throw new \Exception('Cannot validate unknown primitive type');
        }
    }
}
