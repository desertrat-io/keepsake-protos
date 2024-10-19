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

use Avro\Model\Schema\Array_;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;

final class ArrayDenormalizer implements Denormalizer, DenormalizerAware
{
    use HasDenormalizer;

    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return isset($data[Schema::ATTR_TYPE])
            && Array_::TYPE === $data[Schema::ATTR_TYPE]
            && \in_array($targetClass, [Schema::class, Array_::class], true);
    }

    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        if (!isset($data[Array_::ATTR_ITEMS])) {
            throw DenormalizationError::missingField(Array_::ATTR_ITEMS);
        }

        $items = $data[Array_::ATTR_ITEMS];
        if (!\is_array($items)) {
            $items = [Schema::ATTR_TYPE => $items];
        }

        return Array_::of(
            $this->denormalizer->denormalize($items, $targetClass, $context)
        );
    }
}
