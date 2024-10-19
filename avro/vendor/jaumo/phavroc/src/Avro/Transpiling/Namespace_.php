<?php

/*
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

namespace Phavroc\Avro\Transpiling;

final class Namespace_
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromAvroNamespace(string $namespace): self
    {
        return new self(
            implode('\\', explode('.', str_replace('_', '', ucwords($namespace, '_.'))))
        );
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(Namespace_ $other): bool
    {
        return \get_class($this) === \get_class($other)
            && $this->value === $other->value;
    }
}
