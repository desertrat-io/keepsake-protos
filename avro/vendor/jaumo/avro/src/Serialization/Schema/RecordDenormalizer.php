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

use Avro\AvroException;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;

final class RecordDenormalizer implements Denormalizer, DenormalizerAware
{
    use HasDenormalizer;

    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return isset($data[Schema::ATTR_TYPE])
            && (Record::TYPE === $data[Schema::ATTR_TYPE] || Record::TYPE_ERROR === $data[Schema::ATTR_TYPE])
            && \in_array($targetClass, [Schema::class, Record::class], true);
    }

    /**
     * @param array $data
     * @param string $targetClass
     * @param Context|null $context
     * @return Schema
     * @throws AvroException
     */
    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        if (!isset($data[Record::ATTR_NAME])) {
            throw DenormalizationError::missingField(Record::ATTR_NAME);
        }
        if (!isset($data[Record::ATTR_FIELDS])) {
            throw DenormalizationError::missingField(Record::ATTR_FIELDS);
        }

        try {
            $name = NamespacedName::fromValue($data[Record::ATTR_NAME]);
        } catch (\InvalidArgumentException $e) {
            throw DenormalizationError::fromThrowable($e);
        }

        $schema = isset($data[Schema::ATTR_TYPE]) && $data[Schema::ATTR_TYPE] === Record::TYPE_ERROR
            ? Record::namedError($name)
            : Record::named($name);

        if (null === $schema->getNamespace()) {
            if (\array_key_exists(Record::ATTR_NAMESPACE, $data)) {
                $schema = $schema->withNamespace($data[Record::ATTR_NAMESPACE]);
            } elseif ($context instanceof Context && null !== $namespace = $context->getNamespace()) {
                $schema = $schema->withNamespace((string) $namespace);
            }
        }

        if (\array_key_exists(Record::ATTR_DOC, $data)) {
            $schema = $schema->withDoc($data[Record::ATTR_DOC]);
        }

        if (\array_key_exists(Record::ATTR_ALIASES, $data)) {
            $schema = $schema->withAliases($data[Record::ATTR_ALIASES]);
        }

        $context = $context ?? new Context();
        $reference = $context->createReference($schema, function (Record $schema, Context $context) use ($data) {
            if (\array_key_exists(Record::ATTR_FIELDS, $data)) {
                foreach ($data[Record::ATTR_FIELDS] as $field) {
                    if (null !== $schema->getNamespace()) {
                        $context = $context->withNamespace($schema->getNamespace());
                    }

                    /** @var RecordField $recordField */
                    $recordField = $this->denormalizer->denormalize($field, RecordField::class, $context);
                    $schema = $schema->withAddedField($recordField);
                }
            }

            return $schema;
        });

        return $reference->getSchema();
    }
}
