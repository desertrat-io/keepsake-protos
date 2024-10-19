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

namespace Avro;

use Safe;
use Safe\Exceptions\JsonException;

class AvroException extends \Exception
{
    public static function unknownType(string $type): self
    {
        return new self(\sprintf('Unknown schema type "%s"', $type));
    }

    public static function jsonSerializationFailed(JsonException $e): self
    {
        return new self('Failed to serialize data to JSON', 0, $e);
    }

    public static function jsonDeserializationFailed(JsonException $e): self
    {
        return new self('Failed to deserialize data to JSON', 0, $e);
    }

    final protected static function makePrintable($value): string
    {
        try {
            return Safe\json_encode($value);
        } catch (Safe\Exceptions\JsonException $_) {
            return \gettype($value) . ' (not printable)';
        }
    }
}
