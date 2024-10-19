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

use Avro\Model\Schema\Array_;
use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\Map;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\Reference;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;
use LogicException;

class PropertyType
{
    private string $value;
    private bool $scalar = true;
    private bool $combinable = false;
    private ?PropertyLogicalType $logicalType;

    private function __construct(string $value, ?PropertyLogicalType $logicalType)
    {
        $this->value = $value;
        $this->logicalType = $logicalType;
    }

    public static function fromAvroSchema(Schema $schema): self
    {
        $logicalType = null;
        if (
            ($schema instanceof Primitive || $schema instanceof Fixed)
            && null !== $lType = $schema->getLogicalType()
        ) {
            $logicalType = new PropertyLogicalType(
                $lType->getName(),
                $lType->getAttributes()
            );
        }

        switch (true) {
            case $schema instanceof Union:
                // take only non-null type, but always check for nulls, see $normalizeCall and $denormalizeCall in DTONormalization
                return self::fromAvroSchema($schema->getTypes()[1]);

            case $schema instanceof Primitive:
                return self::fromAvroPrimitiveSchema($schema, $logicalType);

            case $schema instanceof Record:
            case $schema instanceof Enum:
            case $schema instanceof Fixed:
                $type = implode('\\', explode('.', str_replace('_', '', ucwords($schema->getFullName(), '_.'))));
                if (false !== strpos($type, '\\')) {
                    $type = '\\'.$type;
                }

                $self = new self($type, $logicalType);
                $self->scalar = false;

                return $self;

            case $schema instanceof Array_:
                $items = $schema->getItems();
                if (!$items instanceof Schema) {
                    throw TranspileError::unknownArrayItemsType((string) \get_class($items));
                }

                $self = self::fromAvroSchema($items);
                $self->combinable = true;

                return $self;

            case $schema instanceof Map:
                $self = self::fromAvroSchema($schema->getValues());
                $self->combinable = true;

                return $self;

            case $schema instanceof Reference:
                return self::fromAvroSchema($schema->getSchema());

            default:
                throw new TranspileError(sprintf(
                    'Cannot create property type from avro schema "%s"',
                    \get_class($schema)
                ));
        }
    }

    public function withValue(string $value): self
    {
        $self = clone $this;
        $self->value = $value;

        return $self;
    }

    private static function fromAvroPrimitiveSchema(
        Primitive $schema,
        ?PropertyLogicalType $logicalType
    ): self {
        switch ($schema->getType()) {
            case Primitive::TYPE_BOOLEAN:
                return new self('bool', $logicalType);

            case Primitive::TYPE_LONG:
            case Primitive::TYPE_INT:
                return new self('int', $logicalType);

            case Primitive::TYPE_DOUBLE:
            case Primitive::TYPE_FLOAT:
                return new self('float', $logicalType);

            case Primitive::TYPE_BYTES:
            case Primitive::TYPE_STRING:
                return new self('string', $logicalType);

            case Primitive::TYPE_NULL:
                return new self('void', $logicalType);
        }

        throw new LogicException('Unknown primitive type ' . $logicalType);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function scalar(): bool
    {
        return $this->scalar;
    }

    public function combinable(): bool
    {
        return $this->combinable;
    }

    public function logicalType(): ?PropertyLogicalType
    {
        return $this->logicalType;
    }
}
