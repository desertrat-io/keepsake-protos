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

namespace Avro;

use Avro\Serialization\Schema\ArrayDenormalizer;
use Avro\Serialization\Schema\ArrayNormalizer;
use Avro\Serialization\Schema\ChainDenormalizer;
use Avro\Serialization\Schema\ChainNormalizer;
use Avro\Serialization\Schema\DefaultSerializer;
use Avro\Serialization\Schema\EnumDenormalizer;
use Avro\Serialization\Schema\EnumNormalizer;
use Avro\Serialization\Schema\FixedDenormalizer;
use Avro\Serialization\Schema\FixedNormalizer;
use Avro\Serialization\Schema\LogicalTypeNormalizer;
use Avro\Serialization\Schema\MapDenormalizer;
use Avro\Serialization\Schema\MapNormalizer;
use Avro\Serialization\Schema\PrimitiveDenormalizer;
use Avro\Serialization\Schema\PrimitiveNormalizer;
use Avro\Serialization\Schema\RecordDenormalizer;
use Avro\Serialization\Schema\RecordFieldDenormalizer;
use Avro\Serialization\Schema\RecordFieldNormalizer;
use Avro\Serialization\Schema\RecordNormalizer;
use Avro\Serialization\Schema\ReferenceDenormalizer;
use Avro\Serialization\Schema\ReferenceNormalizer;
use Avro\Serialization\Schema\UnionDenormalizer;
use Avro\Serialization\Schema\UnionNormalizer;

class SchemaSerializer extends DefaultSerializer
{
    public function __construct()
    {
        parent::__construct(
            new ChainNormalizer([
                new ArrayNormalizer(),
                new EnumNormalizer(),
                new FixedNormalizer(),
                new LogicalTypeNormalizer(),
                new MapNormalizer(),
                new PrimitiveNormalizer(),
                new RecordFieldNormalizer(),
                new RecordNormalizer(),
                new UnionNormalizer(),
                new ReferenceNormalizer(),
            ]),
            new ChainDenormalizer([
                new ArrayDenormalizer(),
                new EnumDenormalizer(),
                new FixedDenormalizer(),
                new MapDenormalizer(),
                new PrimitiveDenormalizer(),
                new RecordDenormalizer(),
                new RecordFieldDenormalizer(),
                new UnionDenormalizer(),
                new ReferenceDenormalizer(),
            ])
        );
    }
}
