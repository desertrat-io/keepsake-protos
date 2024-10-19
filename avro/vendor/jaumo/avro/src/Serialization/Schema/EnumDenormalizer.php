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

use Avro\Model\Schema\Enum;
use Avro\Model\Schema\InvalidSchemaException;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;

final class EnumDenormalizer implements Denormalizer
{
    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return isset($data[Schema::ATTR_TYPE])
            && Enum::TYPE === $data[Schema::ATTR_TYPE]
            && \in_array($targetClass, [Schema::class, Enum::class], true);
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
        if (!isset($data[Enum::ATTR_NAME])) {
            throw DenormalizationError::missingField(Enum::ATTR_NAME);
        }
        if (!isset($data[Enum::ATTR_SYMBOLS])) {
            throw DenormalizationError::missingField(Enum::ATTR_SYMBOLS);
        }

        try {
            $schema = Enum::named(
                NamespacedName::fromValue($data[Enum::ATTR_NAME]),
                \array_map(function (string $symbol): Name {
                    return Name::fromValue($symbol);
                }, $data[Enum::ATTR_SYMBOLS])
            );
        } catch (InvalidSchemaException $e) {
            throw DenormalizationError::fromThrowable($e);
        }

        if (null === $schema->getNamespace()) {
            if (\array_key_exists(Enum::ATTR_NAMESPACE, $data)) {
                $schema = $schema->withNamespace($data[Enum::ATTR_NAMESPACE]);
            } elseif ($context instanceof Context && null !== $namespace = $context->getNamespace()) {
                $schema = $schema->withNamespace((string) $namespace);
            }
        }

        if (\array_key_exists(Enum::ATTR_ALIASES, $data)) {
            $schema = $schema->withAliases($data[Enum::ATTR_ALIASES]);
        }

        if (\array_key_exists(Enum::ATTR_DOC, $data)) {
            $schema = $schema->withDoc($data[Enum::ATTR_DOC]);
        }

        if (null !== $context) {
            $context->createReference($schema);
        }

        return $schema;
    }
}
