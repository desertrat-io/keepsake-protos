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

namespace Avro\Serialization\Protocol;

use Avro\AvroException;
use Avro\Model\Protocol\Protocol;
use Safe;

final class DefaultSerializer implements Serializer
{
    private ProtocolNormalizer $normalizer;
    private ProtocolDenormalizer $denormalizer;

    public function __construct(ProtocolNormalizer $normalizer, ProtocolDenormalizer $denormalizer)
    {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param Protocol $protocol
     * @return string
     * @throws AvroException
     */
    public function serialize(Protocol $protocol): string
    {
        try {
            $data = $this->normalizer->normalize($protocol);

            return Safe\json_encode($data);
        } catch (Safe\Exceptions\JsonException $e) {
            throw AvroException::jsonSerializationFailed($e);
        }
    }

    /**
     * @param string $json
     * @return Protocol
     * @throws AvroException
     */
    public function deserialize(string $json): Protocol
    {
        try {
            $data = Safe\json_decode($json, true);
            $protocol = $this->denormalizer->denormalize($data);

            $hash = \md5($json, true);

            return $protocol->withHash($hash);
        } catch (Safe\Exceptions\JsonException $e) {
            throw AvroException::jsonSerializationFailed($e);
        }
    }
}
