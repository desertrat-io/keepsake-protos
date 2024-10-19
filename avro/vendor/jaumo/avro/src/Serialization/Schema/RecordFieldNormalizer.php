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

use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;

final class RecordFieldNormalizer implements Normalizer, NormalizerAware
{
    use HasNormalizer;

    public function supportsNormalization(Schema $schema): bool
    {
        return $schema instanceof RecordField;
    }

    public function normalize(Schema $schema, bool $canonical = false, ?Context $context = null)
    {
        if (!$schema instanceof RecordField) {
            throw NormalizationError::unsupportedType(\gettype($schema), RecordField::class);
        }

        $normalized = [
            RecordField::ATTR_NAME => $schema->getName(),
            RecordField::ATTR_TYPE => $this->normalizer->normalize($schema->getType(), $canonical, $context),
        ];

        if ($canonical) {
            return $normalized;
        }

        $default = $schema->getDefault();
        if (null !== $default) {
            $normalized[RecordField::ATTR_DEFAULT] = $default->getValue();
        }

        $doc = $schema->getDoc();
        if (null !== $doc) {
            $normalized[RecordField::ATTR_DOC] = $doc;
        }

        $order = $schema->getOrder();
        if (null !== $order) {
            $normalized[RecordField::ATTR_ORDER] = $order->getValue();
        }

        $aliases = $schema->getAliases();
        if (null !== $aliases) {
            $normalized[RecordField::ATTR_ALIASES] = $aliases;
        }

        return $normalized;
    }
}
