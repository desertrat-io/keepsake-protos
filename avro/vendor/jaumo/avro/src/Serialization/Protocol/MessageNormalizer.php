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

use Avro\Model\Protocol\Message;
use Avro\Model\Protocol\Request;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\Normalizer as SchemaNormalizer;

class MessageNormalizer
{
    private SchemaNormalizer $schemaNormalizer;

    public function __construct(SchemaNormalizer $schemaNormalizer)
    {
        $this->schemaNormalizer = $schemaNormalizer;
    }

    /**
     * @param Message $message
     * @return array
     * @throws NormalizationError
     */
    public function normalize(Message $message): array
    {
        $data = [
            Message::ATTR_REQUEST => $this->normalizeRequest($message->getRequest()),
            Message::ATTR_IS_ONE_WAY => $message->isOneWay(),
        ];

        if (null !== $message->getDoc()) {
            $data[Message::ATTR_DOC] = $message->getDoc();
        }

        if (null !== $message->getResponse()) {
            $data[Message::ATTR_RESPONSE] = $this->schemaNormalizer->normalize($message->getResponse());
        }

        if (null !== $message->getErrors()) {
            $data[Message::ATTR_ERRORS] = $this->schemaNormalizer->normalize($message->getErrors());
        }

        return $data;
    }

    /**
     * @param Request $request
     * @return array
     * @throws NormalizationError
     */
    private function normalizeRequest(Request $request): array
    {
        $data = [];
        foreach ($request->getParameters() as $parameter) {
            $data[] = $this->schemaNormalizer->normalize($parameter);
        }

        return $data;
    }
}
