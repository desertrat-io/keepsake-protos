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

namespace Avro\Serialization\Message;

use Avro\Model\Schema\Schema;
use Avro\Serialization\Message\BinaryEncoding\BinaryEncoding;
use Avro\Serialization\Message\BinaryEncoding\ReadError;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;

final class DefaultSerializer implements Serializer
{
    public function serialize(Schema $schema, $message): string
    {
        return BinaryEncoding::encode($schema, $message);
    }

    public function deserialize(string $data, Schema $schema)
    {
        try {
            return BinaryEncoding::decode($schema, new StringByteReader($data));
        } catch (ReadError $e) {
            throw InvalidMessageException::fromThrowable($e);
        }
    }
}
