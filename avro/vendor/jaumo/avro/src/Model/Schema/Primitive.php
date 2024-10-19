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

namespace Avro\Model\Schema;

final class Primitive implements Schema
{
    public const TYPE_NULL = 'null';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INT = 'int';
    public const TYPE_LONG = 'long';
    public const TYPE_FLOAT = 'float';
    public const TYPE_DOUBLE = 'double';
    public const TYPE_BYTES = 'bytes';
    public const TYPE_STRING = 'string';

    public const TYPES = [
        self::TYPE_NULL,
        self::TYPE_BOOLEAN,
        self::TYPE_INT,
        self::TYPE_LONG,
        self::TYPE_FLOAT,
        self::TYPE_DOUBLE,
        self::TYPE_BYTES,
        self::TYPE_STRING,
    ];

    private string $type;

    private ?LogicalType $logicalType = null;

    private function __construct(string $type)
    {
        if (!\in_array($type, self::TYPES, true)) {
            throw new InvalidSchemaException(\sprintf(
                'Type "%s" is not a valid primitive schema type',
                $type
            ));
        }

        $this->type = $type;
    }

    public static function decimal(int $precision, int $scale = 0): self
    {
        $self = new self(self::TYPE_BYTES);
        $self->logicalType = LogicalType::named(self::LOGICAL_TYPE_DECIMAL, [
            self::ATTR_LOGICAL_TYPE_DECIMAL_PRECISION => $precision,
            self::ATTR_LOGICAL_TYPE_DECIMAL_SCALE => $scale,
        ]);

        return $self;
    }

    public static function date(): self
    {
        $self = new self(self::TYPE_INT);
        $self->logicalType = LogicalType::named(self::LOGICAL_TYPE_DATE);

        return $self;
    }

    public static function timeMillis(): self
    {
        $self = new self(self::TYPE_INT);
        $self->logicalType = LogicalType::named(self::LOGICAL_TYPE_TIME_MILLIS);

        return $self;
    }

    public static function timeMicros(): self
    {
        $self = new self(self::TYPE_INT);
        $self->logicalType = LogicalType::named(self::LOGICAL_TYPE_TIME_MICROS);

        return $self;
    }

    public static function timestampMillis(): self
    {
        $self = new self(self::TYPE_LONG);
        $self->logicalType = LogicalType::named(self::LOGICAL_TYPE_TIMESTAMP_MILLIS);

        return $self;
    }

    public static function timestampMicros(): self
    {
        $self = new self(self::TYPE_LONG);
        $self->logicalType = LogicalType::named(self::LOGICAL_TYPE_TIMESTAMP_MICROS);

        return $self;
    }

    public static function custom(string $type, string $logicalType, array $extraAttributes): self
    {
        $self = new self($type);
        $self->logicalType = LogicalType::named($logicalType, $extraAttributes);

        return $self;
    }

    public static function string(): self
    {
        return new self(self::TYPE_STRING);
    }

    public static function long(): self
    {
        return new self(self::TYPE_LONG);
    }

    public static function int(): self
    {
        return new self(self::TYPE_INT);
    }

    public static function null(): self
    {
        return new self(self::TYPE_NULL);
    }

    public static function boolean(): self
    {
        return new self(self::TYPE_BOOLEAN);
    }

    public static function bytes(): self
    {
        return new self(self::TYPE_BYTES);
    }

    public static function float(): self
    {
        return new self(self::TYPE_FLOAT);
    }

    public static function double(): self
    {
        return new self(self::TYPE_DOUBLE);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLogicalType(): ?LogicalType
    {
        return $this->logicalType;
    }
}
