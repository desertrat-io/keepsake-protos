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

use Avro\AvroException;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\RecordFieldDefault;
use Avro\Serialization\Message\BinaryEncoding\RecordEncoding;
use Avro\Serialization\Message\BinaryEncoding\StringByteReader;
use PHPUnit\Framework\TestCase;

class RecordEncodingTest extends TestCase
{
    /**
     * @dataProvider decodeData
     *
     * @throws AvroException
     */
    public function testDecode(string $data, Record $schema, array $expected): void
    {
        $decoded = RecordEncoding::decode($schema, new StringByteReader($data));

        $this->assertEquals($expected, $decoded);
    }

    /**
     * @dataProvider recordData
     *
     * @throws AvroException
     */
    public function testEncode(string $expected, Record $schema, $message): void
    {
        $this->assertEquals($expected, RecordEncoding::encode($schema, $message));
    }

    public function recordData(): array
    {
        $schema = Record::named(NamespacedName::fromValue('test'))
            ->withAddedField(RecordField::named(Name::fromValue('a'), Primitive::long()))
            ->withAddedField(RecordField::named(Name::fromValue('b'), Primitive::string()))
            ->withAddedField(RecordField::named(Name::fromValue('c'), Primitive::string())->withDefault(RecordFieldDefault::fromValue('default')));

        return [
            ["\x36\x06\x66\x6f\x6f\x0e\x64\x65\x66\x61\x75\x6c\x74", $schema, ['a' => 27, 'b' => 'foo']],
            ["\x36\x06\x66\x6f\x6f\x06\x66\x6f\x6f", $schema, ['a' => 27, 'b' => 'foo', 'c' => 'foo']],
        ];
    }

    public function decodeData(): array
    {
        $schema = Record::named(NamespacedName::fromValue('test'))
            ->withAddedField(RecordField::named(Name::fromValue('a'), Primitive::long()))
            ->withAddedField(RecordField::named(Name::fromValue('b'), Primitive::string()))
            ->withAddedField(RecordField::named(Name::fromValue('c'), Primitive::string())->withDefault(RecordFieldDefault::fromValue('default')));

        return [
            ["\x36\x06\x66\x6f\x6f", $schema, ['a' => 27, 'b' => 'foo', 'c' => 'default']],
            ["\x36\x06\x66\x6f\x6f\x06\x66\x6f\x6f", $schema, ['a' => 27, 'b' => 'foo', 'c' => 'foo']],
        ];
    }
}
