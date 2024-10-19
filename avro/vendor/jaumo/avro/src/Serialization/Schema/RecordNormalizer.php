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

use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;

final class RecordNormalizer implements Normalizer, NormalizerAware
{
    use HasNormalizer;

    public function supportsNormalization(Schema $schema): bool
    {
        return $schema instanceof Record;
    }

    public function normalize(Schema $schema, bool $canonical = false, ?Context $context = null): array
    {
        if (!$schema instanceof Record) {
            throw NormalizationError::unsupportedType(\gettype($schema), Record::class);
        }

        $namespace = $schema->getNamespace();

        $normalized = [
            Record::ATTR_NAME => $canonical ? $schema->getFullName() : $schema->getName(),
            Schema::ATTR_TYPE => $schema->isError() ? Record::TYPE_ERROR : Record::TYPE,
            Record::ATTR_FIELDS => \array_map(function (RecordField $field) use ($canonical, $context, $namespace) {
                $context = $context ?? new Context();

                if ($namespace !== null) {
                    $context = $context->withNamespace($namespace);
                }

                return $this->normalizer->normalize($field, $canonical, $context);
            }, $schema->getFields()),
        ];

        if ($canonical) {
            return $normalized;
        }

        if (
            null !== $namespace
            && (null === $context || ($context->hasNamespace() && $namespace !== $context->getNamespace()))
        ) {
            $normalized[Record::ATTR_NAMESPACE] = $namespace;
        }

        if (null !== $aliases = $schema->getAliases()) {
            $normalized[Record::ATTR_ALIASES] = $aliases;
        }

        if (null !== $doc = $schema->getDoc()) {
            $normalized[Record::ATTR_DOC] = $doc;
        }

        return $normalized;
    }
}
