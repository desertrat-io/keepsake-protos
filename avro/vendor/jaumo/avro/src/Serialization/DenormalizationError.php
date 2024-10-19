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

namespace Avro\Serialization;

use Avro\AvroException;

final class DenormalizationError extends AvroException
{
    public static function missingField(string $name): self
    {
        return new self(\sprintf('Cannot denormalize data because it misses a "%s" field', $name));
    }

    public static function invalidDefaultValue($value, string $field): self
    {
        return new self(\sprintf(
            'Cannot denormalize data because default value "%s" of field "%s" is invalid',
            self::makePrintable($value),
            $field
        ));
    }

    public static function denormalizationFailed($data, string $targetClass): self
    {
        return new self(\sprintf(
            'Cannot denormalize "%s" into "%s"',
            self::makePrintable($data),
            $targetClass)
        );
    }

    public static function invalidOrderValue(string $value, string $field): self
    {
        return new self(\sprintf(
            'Cannot denormalize data because order value "%s" of field "%s" is invalid',
            $value,
            $field
        ));
    }

    public static function fromThrowable(\Throwable $throwable): self
    {
        return new self($throwable->getMessage(), $throwable->getCode(), $throwable);
    }

    public static function wrongType(string $name, string $expected, string $actual): self
    {
        return new self(\sprintf(
            'Wrong type provided for attribute "%s" (expected "%s", got "%s")',
            $name,
            $expected,
            $actual
        ));
    }
}
