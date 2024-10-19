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

use Avro\Model\Schema\Array_;

final class ArrayValidating
{
    public static function isValid($value, Array_ $schema): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        $itemSchema = $schema->getItems();
        foreach ($value as $item) {
            if (!Validating::isValid($item, $itemSchema)) {
                return false;
            }
        }

        return true;
    }
}
