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

namespace Avro\Serialization\Message\BinaryEncoding;

class StringByteReader implements ByteReader
{
    /**
     * @var string
     */
    private $bytes;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var int
     */
    private $remaining;

    /**
     * StringByteReader constructor.
     * @param string $bytes
     */
    public function __construct(string $bytes)
    {
        $this->bytes = $bytes;
        $this->remaining = \strlen($bytes);
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $byteCount): string
    {
        if ($byteCount > $this->remaining) {
            throw ReadError::notEnoughBytes($byteCount, $this->remaining);
        }

        $result = \substr($this->bytes, $this->index, $byteCount);

        $this->remaining -= $byteCount;
        $this->index += $byteCount;

        return $result;
    }
}
