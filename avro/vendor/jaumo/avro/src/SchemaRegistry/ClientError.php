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

namespace Avro\SchemaRegistry;

use Avro\AvroException;
use Throwable;

class ClientError extends AvroException
{
    public static function buildRequestFailed(?Throwable $e): self
    {
        return new self('Failed to build an API request', 0, $e);
    }

    public static function jsonParseFailed(string $json, ?Throwable $e): self
    {
        return new self(\sprintf('Failed to parse JSON: "%s"', $json), 0, $e);
    }

    public static function unknownSchema(string $subject, string $schema): self
    {
        return new self(\sprintf(
            'Schema "%s" cannot be found under subject "%s"',
            \strlen($schema) > 50 ? \substr($schema, 0, 50) . '...' : $schema,
            $subject
        ));
    }

    public static function unknownSchemaId(int $id): self
    {
        return new self(\sprintf('Schema #%d does not exist', $id));
    }
}
