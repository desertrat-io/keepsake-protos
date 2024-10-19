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

use Avro\Model\Schema\Reference;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;

final class ReferenceNormalizer implements Normalizer
{
    public function supportsNormalization(Schema $schema): bool
    {
        return $schema instanceof Reference;
    }

    public function normalize(Schema $schema, bool $canonical = false, ?Context $context = null)
    {
        if (!$schema instanceof Reference) {
            throw NormalizationError::unsupportedType(\gettype($schema), Reference::class);
        }

        if ($canonical) {
            return $schema->getFullName();
        }

        $context = $context ?? new Context();
        if ($context->hasNamespace() && $schema->getNamespace() !== $context->getNamespace()) {
            return $schema->getFullName();
        }

        return $schema->getName();
    }
}
