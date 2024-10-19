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

namespace Phavroc\Avro\Transpiling\Transpiler;

use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;
use Phavroc\Avro\Transpiling\HasTranspiler;
use Phavroc\Avro\Transpiling\Package;
use Phavroc\Avro\Transpiling\TranspileError;
use Phavroc\Avro\Transpiling\Transpiler;
use Phavroc\Avro\Transpiling\TranspilerAware;

final class AvroUnionSchema implements Transpiler, TranspilerAware
{
    use HasTranspiler;

    public function transpile(Schema $schema, Package $package): Package
    {
        if (!$schema instanceof Union) {
            return $package;
        }

        $schemas = $schema->getTypes();
        if (2 !== \count($schemas)) {
            throw new TranspileError(sprintf(
                'Union schema transpiler expects a set of exactly 2 schemas, got %d',
                \count($schemas)
            ));
        }

        if (!$schemas[0] instanceof Primitive) {
            throw new TranspileError(sprintf(
                'Union schema transpiler expects first schema to be an instance of "Avro\Model\Schema\Primitive", got "%s"',
                \get_class($schemas[0])
            ));
        }

        if (Primitive::TYPE_NULL !== $schemas[0]->getType()) {
            throw new TranspileError(sprintf(
                'Union schema transpiler expects first schema to be "null", got "%s"',
                $schemas[0]->getType()
            ));
        }

        return $this->transpiler->transpile($schemas[1], $package);
    }
}
