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

use Avro\Model\Schema\Record;

final class RecordValidating
{
    public static function isValid($value, Record $schema): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        foreach ($schema->getFields() as $field) {
            $fieldName = $field->getName();
            if (!\array_key_exists($fieldName, $value)) {
                if (null !== $field->getDefault()) {
                    // Validating the default value is useless, as it has already
                    // been validated while parsing schema
                    continue;
                }

                return false;
            }
            if (!Validating::isValid($value[$fieldName], $field->getType())) {
                return false;
            }
        }

        return true;
    }
}
