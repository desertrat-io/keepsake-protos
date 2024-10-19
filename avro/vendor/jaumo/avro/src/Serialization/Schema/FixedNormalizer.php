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

use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;

final class FixedNormalizer implements Normalizer, NormalizerAware
{
    use HasNormalizer;

    public function supportsNormalization(Schema $schema): bool
    {
        return $schema instanceof Fixed;
    }

    public function normalize(Schema $schema, bool $canonical = false, ?Context $context = null)
    {
        if (!$schema instanceof Fixed) {
            throw NormalizationError::unsupportedType(\gettype($schema), Fixed::class);
        }

        $normalized = [
            Fixed::ATTR_NAME => $canonical ? $schema->getFullName() : $schema->getName(),
            Schema::ATTR_TYPE => Fixed::TYPE,
            Fixed::ATTR_SIZE => $schema->getSize(),
        ];

        if ($canonical) {
            return $normalized;
        }

        if (null !== $namespace = $schema->getNamespace()) {
            if (
                null === $context
                || ($context->hasNamespace() && $namespace !== $context->getNamespace())
            ) {
                $normalized[Fixed::ATTR_NAMESPACE] = $namespace;
            }
        }

        if (null !== $aliases = $schema->getAliases()) {
            $normalized[Fixed::ATTR_ALIASES] = $aliases;
        }

        $logicalType = $schema->getLogicalType();
        if (null !== $logicalType) {
            $normalized = \array_merge($normalized, $this->normalizer->normalize($logicalType, $canonical, $context));
        }

        return $normalized;
    }
}
