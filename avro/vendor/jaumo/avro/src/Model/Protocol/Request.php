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

namespace Avro\Model\Protocol;

use Avro\Model\Schema\RecordField;
use InvalidArgumentException;

final class Request
{
    /** @var RecordField[] */
    private array $parameters;

    private function __construct(array $parameters)
    {
        foreach ($parameters as $parameter) {
            if (!$parameter instanceof RecordField) {
                throw new InvalidArgumentException(\sprintf(
                    'A Request parameter has to be of type "%s" - "%s" given',
                    RecordField::class,
                    \gettype($parameter)
                ));
            }
        }

        $this->parameters = $parameters;
    }

    /**
     * @param RecordField[] $parameters
     * @return Request
     */
    public static function ofParameters(array $parameters): self
    {
        return new self($parameters);
    }

    /**
     * @return RecordField[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
