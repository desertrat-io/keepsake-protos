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

final class TranspileError extends \RuntimeException
{
    public static function unknownFieldType(string $type): self
    {
        return new self(sprintf('Cannot transpile unknown field type "%s"', $type));
    }

    public static function unknownArrayItemsType(string $type): self
    {
        return new self(sprintf('Cannot transpile unknown array items type "%s"', $type));
    }
}
