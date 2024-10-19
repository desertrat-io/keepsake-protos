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

use Avro\Model\Protocol\Protocol;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\Normalizer as SchemaNormalizer;

class ProtocolNormalizer
{
    private SchemaNormalizer $schemaNormalizer;
    private MessageNormalizer $messageNormalizer;

    public function __construct(SchemaNormalizer $schemaNormalizer, MessageNormalizer $messageNormalizer)
    {
        $this->schemaNormalizer = $schemaNormalizer;
        $this->messageNormalizer = $messageNormalizer;
    }

    /**
     * @param Protocol $protocol
     * @return array
     * @throws NormalizationError
     */
    public function normalize(Protocol $protocol): array
    {
        $data = [Protocol::ATTR_NAME => $protocol->getName()];

        if (null !== $protocol->getNamespace()) {
            $data[Protocol::ATTR_NAMESPACE] = $protocol->getNamespace();
        }

        if (null !== $protocol->getDoc()) {
            $data[Protocol::ATTR_DOC] = $protocol->getDoc();
        }

        if (\count($protocol->getTypes()) > 0) {
            $data[Protocol::ATTR_TYPES] = [];

            foreach ($protocol->getTypes() as $type) {
                $data[Protocol::ATTR_TYPES][] = $this->schemaNormalizer->normalize($type);
            }
        }

        if (\count($protocol->getMessages()) > 0) {
            $data[Protocol::ATTR_MESSAGES] = [];

            foreach ($protocol->getMessages() as $message) {
                $data[Protocol::ATTR_MESSAGES][$message->getName()->getValue()] = $this->messageNormalizer->normalize($message);
            }
        }

        return $data;
    }
}
