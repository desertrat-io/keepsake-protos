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

namespace AvroTest\Serialization\Message\BinaryEncoding;

use Avro\Serialization\Message\BinaryEncoding\ReadError;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use PHPUnit\Framework\TestCase;

class StringByteReaderTest extends TestCase
{
    /**
     * @throws ReadError
     */
    public function testRead(): void
    {
        $reader = new StringByteReader('test');

        $this->assertEquals('t', $reader->read(1));
        $this->assertEquals('est', $reader->read(3));
    }

    /**
     * @throws ReadError
     */
    public function testReadOutOfBounds(): void
    {
        $this->expectException(ReadError::class);

        $reader = new StringByteReader('test');
        $reader->read(5);
    }
}
