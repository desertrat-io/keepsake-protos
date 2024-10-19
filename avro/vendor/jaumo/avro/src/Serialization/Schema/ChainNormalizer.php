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

final class ChainNormalizer implements Normalizer
{
    private array $normalizers;
    private array $cachedNormalizers = [];

    public function __construct(array $normalizers)
    {
        foreach ($normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerAware) {
                $normalizer->setNormalizer($this);
            }
        }
        $this->normalizers = $normalizers;
    }

    public function supportsNormalization(Schema $schema): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(Schema $schema, bool $canonical = false, ?Context $context = null)
    {
        return $this->getNormalizer($schema)->normalize($schema, $canonical, $context);
    }

    private function getNormalizer(Schema $schema): Normalizer
    {
        $class = \get_class($schema);
        if (!isset($this->cachedNormalizers[$class])) {
            foreach ($this->normalizers as $normalizer) {
                if ($normalizer->supportsNormalization($schema)) {
                    $this->cachedNormalizers[$class] = $normalizer;
                    break;
                }
            }
        }

        if (!isset($this->cachedNormalizers[$class])) {
            throw new \RuntimeException('No normalizer found');
        }

        return $this->cachedNormalizers[$class];
    }
}
