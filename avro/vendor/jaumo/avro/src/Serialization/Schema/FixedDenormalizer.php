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

namespace Avro\Serialization\Schema;

use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\InvalidSchemaException;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;

final class FixedDenormalizer implements Denormalizer
{
    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return isset($data[Schema::ATTR_TYPE])
            && Fixed::TYPE === $data[Schema::ATTR_TYPE]
            && \in_array($targetClass, [Schema::class, Fixed::class], true);
    }

    /**
     * @param array $data
     * @param string $targetClass
     * @param Context|null $context
     * @return Schema
     * @throws DenormalizationError
     */
    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        if (!isset($data[Fixed::ATTR_NAME])) {
            throw DenormalizationError::missingField(Fixed::ATTR_NAME);
        }
        try {
            $name = NamespacedName::fromValue($data[Fixed::ATTR_NAME]);
        } catch (InvalidSchemaException $e) {
            throw DenormalizationError::fromThrowable($e);
        }

        if (
            isset($data[Schema::ATTR_LOGICAL_TYPE])
            && Schema::LOGICAL_TYPE_DURATION === $data[Schema::ATTR_LOGICAL_TYPE]
        ) {
            $schema = Fixed::duration($name);
        } else {
            if (!isset($data[Fixed::ATTR_SIZE])) {
                throw DenormalizationError::missingField(Fixed::ATTR_SIZE);
            }
            if (!\is_int($data[Fixed::ATTR_SIZE])) {
                throw DenormalizationError::wrongType(Fixed::ATTR_SIZE, 'integer', \gettype($data[Fixed::ATTR_SIZE]));
            }
            $schema = Fixed::named($name, $data[Fixed::ATTR_SIZE]);
        }

        if (null === $schema->getNamespace()) {
            if (\array_key_exists(Fixed::ATTR_NAMESPACE, $data)) {
                $schema = $schema->withNamespace($data[Fixed::ATTR_NAMESPACE]);
            } elseif ($context instanceof Context && null !== $namespace = $context->getNamespace()) {
                $schema = $schema->withNamespace((string) $namespace);
            }
        }

        if (\array_key_exists(Fixed::ATTR_ALIASES, $data)) {
            $schema = $schema->withAliases($data[Fixed::ATTR_ALIASES]);
        }

        if (null !== $context) {
            $context->createReference($schema);
        }

        return $schema;
    }
}
