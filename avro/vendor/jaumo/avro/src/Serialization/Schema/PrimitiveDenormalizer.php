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

namespace Avro\Serialization\Schema;

use Avro\Model\Schema\InvalidSchemaException;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;

final class PrimitiveDenormalizer implements Denormalizer
{
    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return isset($data[Schema::ATTR_TYPE])
            && \in_array($data[Schema::ATTR_TYPE], Primitive::TYPES, true)
            && \in_array($targetClass, [Schema::class, Primitive::class], true);
    }

    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        if (!isset($data[Schema::ATTR_TYPE])) {
            throw DenormalizationError::missingField(Schema::ATTR_TYPE);
        }

        if (isset($data[Schema::ATTR_LOGICAL_TYPE])) {
            switch ($data[Schema::ATTR_LOGICAL_TYPE]) {
                case Schema::LOGICAL_TYPE_DECIMAL:
                    if (Primitive::TYPE_BYTES !== $data[Schema::ATTR_TYPE]) {
                        break;
                    }

                    $precision = $data[Schema::ATTR_LOGICAL_TYPE_DECIMAL_PRECISION];
                    $scale = $data[Schema::ATTR_LOGICAL_TYPE_DECIMAL_SCALE] ?? 0;

                    if ($precision < $scale) {
                        break;
                    }

                    return Primitive::decimal($precision, $scale);

                case Schema::LOGICAL_TYPE_DATE:
                    if (Primitive::TYPE_INT !== $data[Schema::ATTR_TYPE]) {
                        break;
                    }

                    return Primitive::date();

                case Schema::LOGICAL_TYPE_TIME_MILLIS:
                    if (Primitive::TYPE_INT !== $data[Schema::ATTR_TYPE]) {
                        break;
                    }

                    return Primitive::timeMillis();

                case Schema::LOGICAL_TYPE_TIME_MICROS:
                    if (Primitive::TYPE_INT !== $data[Schema::ATTR_TYPE]) {
                        break;
                    }

                    return Primitive::timeMicros();

                case Schema::LOGICAL_TYPE_TIMESTAMP_MILLIS:
                    if (Primitive::TYPE_LONG !== $data[Schema::ATTR_TYPE]) {
                        break;
                    }

                    return Primitive::timestampMillis();

                case Schema::LOGICAL_TYPE_TIMESTAMP_MICROS:
                    if (Primitive::TYPE_LONG !== $data[Schema::ATTR_TYPE]) {
                        break;
                    }

                    return Primitive::timestampMicros();

                default:
                    return Primitive::custom(
                        $data[Schema::ATTR_TYPE],
                        $data[Schema::ATTR_LOGICAL_TYPE],
                        \array_filter(
                            $data,
                            function (string $key): bool {
                                return !\in_array($key, [Schema::ATTR_TYPE, Schema::ATTR_LOGICAL_TYPE], true);
                            },
                            ARRAY_FILTER_USE_KEY
                        )
                    );
            }
        }

        try {
            return Primitive::fromString($data[Schema::ATTR_TYPE]);
        } catch (InvalidSchemaException $e) {
            throw DenormalizationError::fromThrowable($e);
        }
    }
}
