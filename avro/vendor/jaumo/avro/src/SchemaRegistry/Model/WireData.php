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

namespace Avro\SchemaRegistry\Model;

use Avro\SchemaRegistry\SerializationError;

class WireData
{
    private const PACK_FORMAT = 'CNa*';
    private const UNPACK_FORMAT = 'Cmagic/NschemaId/a*data';
    private const MAGIC_BYTE = 0x00;

    private int $schemaId;
    private string $message;

    /**
     * Data constructor.
     * @param int $schemaId
     * @param string $message
     */
    public function __construct(int $schemaId, string $message)
    {
        $this->schemaId = $schemaId;
        $this->message = $message;
    }

    public static function fromBinary(string $binary): self
    {
        [
            'magic' => $magic,
            'schemaId' => $schemaId,
            'data' => $data
        ] = \unpack(self::UNPACK_FORMAT, $binary);

        if ($magic !== self::MAGIC_BYTE) {
            throw SerializationError::magicByteNotFound();
        }

        return new self($schemaId, $data);
    }

    public function toBinary(): string
    {
        return \pack(self::PACK_FORMAT, self::MAGIC_BYTE, $this->schemaId, $this->message);
    }

    /**
     * @return int
     */
    public function getSchemaId(): int
    {
        return $this->schemaId;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
