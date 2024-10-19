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
use Avro\Serialization\Protocol\MessageNormalizer;
use Avro\Serialization\Protocol\ProtocolNormalizer;
use Avro\Serialization\Schema\Normalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProtocolNormalizerTest extends TestCase
{
    /** @var ProtocolNormalizer */
    private $normalizer;

    /** @var Normalizer|MockObject */
    private $schemaNormalizer;

    /** @var MessageNormalizer|MockObject */
    private $messageNormalizer;

    protected function setUp(): void
    {
        $this->schemaNormalizer = $this->createMock(Normalizer::class);
        $this->schemaNormalizer
            ->method('normalize')->willReturn(['PLACEHOLDER']);

        $this->messageNormalizer = $this->createMock(MessageNormalizer::class);
        $this->messageNormalizer
            ->method('normalize')->willReturn(['PLACEHOLDER']);

        $this->normalizer = new ProtocolNormalizer($this->schemaNormalizer, $this->messageNormalizer);
    }

    /**
     * @dataProvider exampleProtocols
     *
     * @param Protocol $protocol
     * @param array $expected
     */
    public function testNormalize(Protocol $protocol, array $expected): void
    {
        $this->assertEquals($expected, $this->normalizer->normalize($protocol));
    }

    public function exampleProtocols(): array
    {
        return [
            [
                Protocol::named(NamespacedName::fromValue('test'))
                    ->withDoc('doc!')
                    ->withType($this->createMock(Named::class))
                    ->withMessage(Message::oneWay(Name::fromValue('message'), Request::ofParameters([]))),
                [
                    'protocol' => 'test',
                    'doc' => 'doc!',
                    'types' => [['PLACEHOLDER']],
                    'messages' => [
                        'message' => ['PLACEHOLDER'],
                    ],
                ],
            ],
        ];
    }
}
