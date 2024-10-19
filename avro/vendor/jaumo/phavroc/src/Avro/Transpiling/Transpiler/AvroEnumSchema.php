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

use Avro\Model\Schema\Enum as EnumSchema;
use Avro\Model\Schema\Schema;
use Phavroc\Avro\Transpiling\ClassName;
use Phavroc\Avro\Transpiling\Enum;
use Phavroc\Avro\Transpiling\HasTranspiler;
use Phavroc\Avro\Transpiling\Namespace_;
use Phavroc\Avro\Transpiling\Package;
use Phavroc\Avro\Transpiling\Transpiler;
use Phavroc\Avro\Transpiling\TranspilerAware;

final class AvroEnumSchema implements Transpiler, TranspilerAware
{
    use HasTranspiler;

    public function transpile(Schema $schema, Package $package): Package
    {
        if (!$schema instanceof EnumSchema) {
            return $package;
        }

        $namespace = Namespace_::fromAvroNamespace($schema->getNamespace() ?? '');
        $name = ClassName::fromAvroName($schema->getName());

        $class = Enum::create(
            $name,
            $namespace,
            $schema->getDoc(),
            \Avro\Serde::dumpSchema($schema),
            array_map('strval', $schema->getSymbols())
        );
        if (null !== $commonInterface = $package->commonInterface()) {
            $class = $class->withInterface((string) $commonInterface);
        }
        $package = $package->withAddedClass($class);

        return $package;
    }
}
