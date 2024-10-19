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

final class Name
{
    private string $value;

    private function __construct(string $value)
    {
        if (empty($value) || 0 === \preg_match('/^[A-Za-z_]\w*$/', $value)) {
            throw new InvalidSchemaException(\sprintf('Name "%s" is invalid', $value));
        }

        $this->value = $value;
    }

    public static function fromValue(string $name): self
    {
        return new self($name);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
