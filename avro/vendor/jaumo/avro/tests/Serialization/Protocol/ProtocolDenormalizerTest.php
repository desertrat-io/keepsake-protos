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

use Avro\Model\Protocol\Message;
use Avro\Model\Protocol\Protocol;
use Avro\Model\Protocol\Request;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\Named;
use Avro\Model\Schema\NamespacedName;
use Avro\Serialization\Context;
use Avro\Serialization\Protocol\MessageDenormalizer;
use Avro\Serialization\Protocol\ProtocolDenormalizer;
use Avro\Serialization\Schema\Denormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProtocolDenormalizerTest extends TestCase
{
    private const EXAMPLE_TYPE_NAME = 'ExampleType';

    /** @var ProtocolDenormalizer */
    private $denormalizer;

    /** @var Denormalizer|MockObject */
    private $schemaDenormalizer;

    /** @var MessageDenormalizer|MockObject */
    private $messageDenormalizer;

    /** @var Message */
    private $exampleMessage;

    /** @var Named|MockObject */
    private $typeMock;

    protected function setUp(): void
    {
        $this->exampleMessage = Message::oneWay(Name::fromValue('message'), Request::ofParameters([]));

        $this->typeMock = $this->createMock(Named::class);
        $this->typeMock->method('getName')->willReturn(self::EXAMPLE_TYPE_NAME);
        $this->typeMock->method('getFullName')->willReturn(self::EXAMPLE_TYPE_NAME);

        $this->schemaDenormalizer = $this->createMock(Denormalizer::class);
        $this->schemaDenormalizer
            ->method('denormalize')->willReturn($this->typeMock);

        $this->messageDenormalizer = $this->createMock(MessageDenormalizer::class);
        $this->messageDenormalizer
            ->method('denormalize')->willReturn($this->exampleMessage);

        $this->denormalizer = new ProtocolDenormalizer($this->schemaDenormalizer, $this->messageDenormalizer);
    }

    /**
     * @throws \Avro\AvroException
     */
    public function testDenormalize(): void
    {
        $data = [
            'protocol' => 'test',
            'doc' => 'doc!',
            'types' => [['PLACEHOLDER']],
            'messages' => [
                'message' => ['PLACEHOLDER'],
            ],
        ];

        $expected = Protocol::named(NamespacedName::fromValue('test'))
            ->withDoc('doc!')
            ->withType($this->typeMock)
            ->withMessage($this->exampleMessage);

        $context = new Context();
        $this->assertEquals($expected, $this->denormalizer->denormalize($data, $context));
    }
}
