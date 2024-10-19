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

require __DIR__ . '/../vendor/autoload.php';

use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Union;

$schema = Record::named(NamespacedName::fromValue('com.avro.Person'))
    ->withAddedField(RecordField::named(
        Name::fromValue('name'),
        Union::of([
            Primitive::null(),
            Primitive::string(),
        ])
    ))
    ->withAddedField(RecordField::named(
        Name::fromValue('gender'),
        Union::of([
            Primitive::null(),
            Enum::named(NamespacedName::fromValue('Gender'), [
                Name::fromValue('FEMALE'),
                Name::fromValue('MALE'),
            ]),
        ])
    ));

$data = \Avro\Serde::encodeMessage($schema, ['name' => 'John Doe', 'gender' => 'MALE']);
(\var_dump($data));
