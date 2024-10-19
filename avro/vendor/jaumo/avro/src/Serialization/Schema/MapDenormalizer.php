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

use Avro\Model\Schema\Map;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;

final class MapDenormalizer implements Denormalizer, DenormalizerAware
{
    use HasDenormalizer;

    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return isset($data[Schema::ATTR_TYPE])
            && Map::TYPE === $data[Schema::ATTR_TYPE]
            && \in_array($targetClass, [Schema::class, Map::class], true);
    }

    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        if (!isset($data[Map::ATTR_VALUES])) {
            throw DenormalizationError::missingField(Map::ATTR_VALUES);
        }

        $values = $data[Map::ATTR_VALUES];
        if (!\is_array($values)) {
            $values = [Schema::ATTR_TYPE => $values];
        }

        return Map::to(
            $this->denormalizer->denormalize($values, $targetClass, $context)
        );
    }
}
