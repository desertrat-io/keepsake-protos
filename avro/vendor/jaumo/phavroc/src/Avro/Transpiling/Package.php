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

final class Package
{
    private string $commonInterface;

    /** @var array<Class_> */
    private array $classes = [];

    public function withCommonInterface(string $commonInterface): self
    {
        $self = clone $this;
        $self->commonInterface = $commonInterface;

        return $self;
    }

    public function withAddedClass(Class_ $class): self
    {
        if ($this->hasClass($class)) {
            return $this;
        }
        $self = clone $this;
        $self->classes[] = $class;

        return $self;
    }

    public function hasClass(Class_ $aClass): bool
    {
        foreach ($this->classes as $class) {
            if ($class->equals($aClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Class_[]
     */
    public function classes(): array
    {
        return $this->classes;
    }

    /**
     * @return string|null
     */
    public function commonInterface(): ?string
    {
        return $this->commonInterface;
    }
}
