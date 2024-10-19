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
use Avro\Serialization\Context;

final class ChainDenormalizer implements Denormalizer
{
    private array $denormalizers;

    public function __construct(array $denormalizers)
    {
        foreach ($denormalizers as $denormalizer) {
            if ($denormalizer instanceof DenormalizerAware) {
                $denormalizer->setDenormalizer($this);
            }
        }
        $this->denormalizers = $denormalizers;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->supportsDenormalization($data, $targetClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->supportsDenormalization($data, $targetClass)) {
                return $denormalizer->denormalize($data, $targetClass, $context);
            }
        }

        throw new \RuntimeException('no denormalizer found');
    }
}
