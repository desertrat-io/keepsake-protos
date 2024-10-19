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

use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;
use Avro\Serialization\Context;

final class UnionDenormalizer implements Denormalizer, DenormalizerAware
{
    use HasDenormalizer;

    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return !\array_key_exists(Schema::ATTR_TYPE, $data)
            && $data === \array_values($data)
            && \in_array($targetClass, [Schema::class, Union::class], true);
    }

    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        return Union::of(\array_map(function ($type) use ($context) {
            if (!\is_array($type)) {
                $type = [Schema::ATTR_TYPE => $type];
            }

            return $this->denormalizer->denormalize($type, Schema::class, $context);
        }, $data));
    }
}
