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

namespace AvroTest\Serialization\Protocol;

use Avro\AvroException;
use Avro\Model\Protocol\Message;
use Avro\Model\Protocol\Request;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Protocol\MessageDenormalizer;
use Avro\Serialization\Schema\Denormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageDenormalizerTest extends TestCase
{
    /** @var MessageDenormalizer */
    private $denormalizer;

    /** @var Denormalizer|MockObject */
    private $schemaNormalizer;

    /** @var Schema|MockObject */
    private $schemaMock;

    /** @var Union */
    private $unionMock;

    /** @var RecordField */
    private $recordFieldMock;

    /** @var Name */
    private $messageName;

    protected function setUp(): void
    {
        $this->schemaMock = $this->createMock(Schema::class);
        $this->unionMock = Union::of([Primitive::null()]);
        $this->recordFieldMock = RecordField::named(Name::fromValue('MockField'), Primitive::null());
        $this->messageName = Name::fromValue('hello');

        $this->schemaNormalizer = $this->createMock(Denormalizer::class);
        $this->schemaNormalizer
            ->method('denormalize')->willReturnCallback(
                function (/** @noinspection PhpUnusedParameterInspection */ array $data, string $type) {
                    switch ($type) {
                        case RecordField::class:
                            return $this->recordFieldMock;
                        case Union::class:
                            return $this->unionMock;
                        default:
                            return $this->schemaMock;
                    }
                });

        $this->denormalizer = new MessageDenormalizer($this->schemaNormalizer);
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizeOneWay(): void
    {
        $data = [
            'request' => [['PLACEHOLDER']],
            'doc' => 'Some documentation',
            'one-way' => true,
        ];

        $expected = Message::oneWay(
            Name::fromValue('hello'),
            Request::ofParameters([$this->recordFieldMock])
        )->withDoc('Some documentation');

        $this->assertEquals($expected, $this->denormalizer->denormalize($this->messageName, $data, new Context()));
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizeTwoWay(): void
    {
        $data = [
            'request' => [['PLACEHOLDER'], ['PLACEHOLDER']],
            'errors' => ['PLACEHOLDER'],
            'response' => ['PLACEHOLDER'],
        ];

        $expected = Message::twoWay(
            $this->messageName,
            Request::ofParameters([$this->recordFieldMock, $this->recordFieldMock]),
            $this->schemaMock,
            $this->unionMock
        );

        $this->assertEquals($expected, $this->denormalizer->denormalize($this->messageName, $data, new Context()));
    }

    /**
     * @throws AvroException
     */
    public function testDenormalizeTwoWayError(): void
    {
        $data = [
            'request' => [['PLACEHOLDER'], ['PLACEHOLDER']],
            'one-way' => true,
            'errors' => ['PLACEHOLDER'],
            'response' => ['PLACEHOLDER'],
        ];

        $this->expectException(DenormalizationError::class);
        $this->denormalizer->denormalize($this->messageName, $data, new Context());
    }
}
