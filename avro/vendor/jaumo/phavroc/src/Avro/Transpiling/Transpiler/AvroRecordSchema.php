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

use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordFieldDefault;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;
use Phavroc\Avro\Transpiling\ClassName;
use Phavroc\Avro\Transpiling\DTO;
use Phavroc\Avro\Transpiling\HasTranspiler;
use Phavroc\Avro\Transpiling\Namespace_;
use Phavroc\Avro\Transpiling\Package;
use Phavroc\Avro\Transpiling\Property;
use Phavroc\Avro\Transpiling\PropertyName;
use Phavroc\Avro\Transpiling\PropertyType;
use Phavroc\Avro\Transpiling\Transpiler;
use Phavroc\Avro\Transpiling\TranspilerAware;

final class AvroRecordSchema implements Transpiler, TranspilerAware
{
    use HasTranspiler;

    public function transpile(Schema $schema, Package $package): Package
    {
        if (!$schema instanceof Record) {
            return $package;
        }

        $namespace = Namespace_::fromAvroNamespace($schema->getNamespace() ?? '');
        $name = ClassName::fromAvroName($schema->getName());

        $class = DTO::create($name, $namespace, $schema->getDoc(), \Avro\Serde::dumpSchema($schema));
        if ($package->hasClass($class)) {
            return $package;
        }

        $namespace = $class->namespace();
        foreach ($schema->getFields() as $field) {
            switch (true) {
                case $field->getType() instanceof Union:
                    $secondSchema = $field->getType()->getTypes()[1];
                    $property = Property::fromAvroType(
                        PropertyName::fromAvroName($field->getName()),
                        PropertyType::fromAvroSchema($secondSchema),
                        $field->getDoc()
                    );
                    $property = $property->withNullable(true);
                    break;

                default:
                    $property = Property::fromAvroType(
                        PropertyName::fromAvroName($field->getName()),
                        PropertyType::fromAvroSchema($field->getType()),
                        $field->getDoc()
                    );
            }

            $type = $property->type();
            if (
                (false !== $pos = strrpos($type, '\\'))
                && substr($type, 1, $pos - 1) === $namespace
            ) {
                $property = $property->withType(substr($type, $pos + 1));
            }

            $default = $field->getDefault();
            if ($default instanceof RecordFieldDefault) {
                $property = $property->withDefaultValue($default->getValue());
            }

            $class = $class->withAddedProperty($property);
        }

        if (null !== $commonInterface = $package->commonInterface()) {
            $class = $class->withInterface((string) $commonInterface);
        }

        if (!$package->hasClass($class)) {
            $package = $package->withAddedClass($class);
        }

        foreach ($schema->getFields() as $field) {
            $package = $this->transpiler->transpile($field, $package);
        }

        return $package;
    }
}
