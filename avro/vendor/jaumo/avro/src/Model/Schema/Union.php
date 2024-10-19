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

final class Union implements Schema
{
    private array $types = [];

    private function __construct(array $types)
    {
        $encounteredTypes = [];
        foreach ($types as $position => $type) {
            if (!$type instanceof Schema) {
                throw new InvalidSchemaException(\sprintf(
                    'Expected types to be an array of "Avro\Model\Schema", got "%s" at position %d',
                    \is_object($type) ? \get_class($type) : \gettype($type),
                    $position
                ));
            }

            if ($type instanceof self) {
                throw new InvalidSchemaException('Unions may not immediately contain other unions');
            }

            $currentType = \get_class($type);
            if ($type instanceof Primitive) {
                $currentType = $type->getType();
            }
            if (\in_array($currentType, $encounteredTypes, true)) {
                throw new InvalidSchemaException('Unions may not contain more than one schema with the same type');
            }

            if (!$type instanceof Named) {
                $encounteredTypes[] = $currentType;
            }
        }

        $this->types = $types;
    }

    public static function of(array $types): self
    {
        return new self($types);
    }

    public function getTypes(): array
    {
        return $this->types;
    }
}
