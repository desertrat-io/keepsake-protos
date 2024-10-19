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

use Avro\Model\Schema\InvalidSchemaException;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\RecordFieldDefault;
use Avro\Model\Schema\RecordFieldOrder;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;

final class RecordFieldDenormalizer implements Denormalizer, DenormalizerAware
{
    use HasDenormalizer;

    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return RecordField::class === $targetClass;
    }

    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        if (!isset($data[Schema::ATTR_TYPE])) {
            throw DenormalizationError::missingField(Schema::ATTR_TYPE);
        }

        if (!isset($data[RecordField::ATTR_NAME])) {
            throw DenormalizationError::missingField(RecordField::ATTR_NAME);
        }

        if (!\is_array($data[Schema::ATTR_TYPE])) {
            $data[Schema::ATTR_TYPE] = [Schema::ATTR_TYPE => $data[Schema::ATTR_TYPE]];
        }

        try {
            $name = Name::fromValue($data[RecordField::ATTR_NAME]);
        } catch (InvalidSchemaException $e) {
            throw DenormalizationError::fromThrowable($e);
        }

        $type = $this->denormalizer->denormalize($data[Schema::ATTR_TYPE], Schema::class, $context);
        $field = RecordField::named($name, $type);

        if (\array_key_exists(RecordField::ATTR_DOC, $data)) {
            $field = $field->withDoc($data[RecordField::ATTR_DOC]);
        }

        if (\array_key_exists(RecordField::ATTR_DEFAULT, $data)) {
            try {
                $field = $field->withDefault(RecordFieldDefault::fromValue($data[RecordField::ATTR_DEFAULT]));
            } catch (InvalidSchemaException $e) {
                throw DenormalizationError::invalidDefaultValue($data[RecordField::ATTR_DEFAULT], $data[RecordField::ATTR_NAME]);
            }
        }

        if (\array_key_exists(RecordField::ATTR_ORDER, $data)) {
            try {
                $field = $field->withOrder(RecordFieldOrder::fromValue($data[RecordField::ATTR_ORDER]));
            } catch (InvalidSchemaException $e) {
                throw DenormalizationError::invalidOrderValue($data[RecordField::ATTR_ORDER], $data[RecordField::ATTR_NAME]);
            }
        }

        if (\array_key_exists(RecordField::ATTR_ALIASES, $data)) {
            $field = $field->withAliases($data[RecordField::ATTR_ALIASES]);
        }

        return $field;
    }
}
