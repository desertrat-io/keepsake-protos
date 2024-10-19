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

use Avro\AvroException;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Safe;

class DefaultSerializer implements Serializer
{
    private Normalizer $normalizer;
    private Denormalizer $denormalizer;

    public function __construct(Normalizer $normalizer, Denormalizer $denormalizer)
    {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    public function serialize(Schema $schema, bool $canonical = false): string
    {
        try {
            $data = $this->normalizer->normalize($schema, $canonical);

            return Safe\json_encode($data);
        } catch (Safe\Exceptions\JsonException $e) {
            throw AvroException::jsonSerializationFailed($e);
        }
    }

    public function deserialize(string $json): Schema
    {
        try {
            $data = Safe\json_decode($json, true);

            return $this->denormalizer->denormalize($data);
        } catch (Safe\Exceptions\JsonException $e) {
            throw AvroException::jsonDeserializationFailed($e);
        }
    }

    public function supportsNormalization(Schema $schema): bool
    {
        return $this->normalizer->supportsNormalization($schema);
    }

    public function normalize(Schema $schema, bool $canonical = false, ?Context $context = null)
    {
        return $this->normalizer->normalize($schema, $canonical, $context ?? new Context());
    }

    public function supportsDenormalization(array $data, string $targetClass): bool
    {
        return $this->denormalizer->supportsDenormalization($data, $targetClass);
    }

    public function denormalize(array $data, string $targetClass = Schema::class, ?Context $context = null): Schema
    {
        return $this->denormalizer->denormalize($data, $targetClass, $context ?? new Context());
    }
}
