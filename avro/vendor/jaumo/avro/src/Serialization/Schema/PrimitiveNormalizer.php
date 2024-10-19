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

use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;

final class PrimitiveNormalizer implements Normalizer, NormalizerAware
{
    use HasNormalizer;

    public function supportsNormalization(Schema $schema): bool
    {
        return $schema instanceof Primitive;
    }

    public function normalize(Schema $schema, bool $canonical = false, ?Context $context = null)
    {
        if (!$schema instanceof Primitive) {
            throw NormalizationError::unsupportedType(\gettype($schema), Primitive::class);
        }

        if (!$canonical && null === $schema->getLogicalType()) {
            return $schema->getType();
        }

        $normalized = [Schema::ATTR_TYPE => $schema->getType()];

        if (null === $schema->getLogicalType()) {
            return $normalized;
        }

        if (!$canonical) {
            $normalized = \array_merge(
                $normalized,
                $this->normalizer->normalize($schema->getLogicalType(), $canonical, $context)
            );
        }

        return $normalized;
    }
}
