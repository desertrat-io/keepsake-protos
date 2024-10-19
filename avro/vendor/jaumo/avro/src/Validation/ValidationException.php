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
use Avro\Model\Schema\Schema;

final class ValidationException extends AvroException
{
    public static function fromValue($value, Schema $schema): self
    {
        return new self(\sprintf(
            'Value %s does not validate against the "%s" schema',
            self::makePrintable($value),
            \get_class($schema)
        ));
    }

    public static function unknownRecordField(string $name): self
    {
        return new self(\sprintf('Record field "%s" is missing from value', $name));
    }

    public static function unexpectedSchema(string $givenSchema, string $expectedSchema): self
    {
        return new self(\sprintf('Expected schema of type "%s", got "%s"', $expectedSchema, $givenSchema));
    }
}
