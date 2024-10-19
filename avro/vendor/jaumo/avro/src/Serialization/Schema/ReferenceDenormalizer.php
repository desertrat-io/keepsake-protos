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
use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\Map;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;

final class ReferenceDenormalizer implements Denormalizer
{
    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return Schema::class === $targetClass &&
            isset($data[Schema::ATTR_TYPE]) &&
            !\in_array($data[Schema::ATTR_TYPE], Primitive::TYPES, true) &&
            !\in_array($data[Schema::ATTR_TYPE], [Array_::TYPE, Enum::TYPE, Fixed::TYPE, Map::TYPE, Record::TYPE],
                true);
    }

    /**
     * @param array $data
     * @param string $targetClass
     * @param Context|null $context
     * @return Schema
     * @throws DenormalizationError
     */
    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        $name = NamespacedName::fromValue($data[Schema::ATTR_TYPE]);

        if (null !== $context && null !== $context->getNamespace() && null === $name->getNamespace()) {
            $name = $name->withNamespace($context->getNamespace());
        }

        if ($context !== null) {
            $reference = $context->getReference($name);

            if (null !== $reference) {
                return $reference;
            }
        }

        throw new DenormalizationError(\sprintf('Unable to resolve referenced type "%s"', $name->getFullName()));
    }
}
