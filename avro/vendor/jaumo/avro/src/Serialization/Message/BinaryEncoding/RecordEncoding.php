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

namespace Avro\Serialization\Message\BinaryEncoding;

use Avro\AvroException;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordFieldDefault;
use Avro\Validation\ValidationException;

class RecordEncoding
{
    /**
     * @return string
     * @throws AvroException
     */
    public static function encode(Record $schema, array $value): string
    {
        $result = '';
        foreach ($schema->getFields() as $field) {
            $fieldName = $field->getName();
            if (!\array_key_exists($fieldName, $value)) {
                $default = $field->getDefault();
                if (!$default instanceof RecordFieldDefault) {
                    throw ValidationException::unknownRecordField($fieldName);
                }
                $value[$fieldName] = $default->getValue();
            }

            $result .= BinaryEncoding::encode(
                $field->getType(),
                $value[$fieldName]
            );
        }

        return $result;
    }

    /**
     * @param Record $schema
     * @param ByteReader $reader
     * @return array
     * @throws AvroException
     */
    public static function decode(Record $schema, ByteReader $reader): array
    {
        $records = [];

        foreach ($schema->getFields() as $field) {
            try {
                $records[$field->getName()] = BinaryEncoding::decode($field->getType(), $reader);
            } catch (ReadError $e) {
                if ($field->getDefault() === null) {
                    throw $e;
                }

                $records[$field->getName()] = $field->getDefault()->getValue();
            }
        }

        return $records;
    }
}
