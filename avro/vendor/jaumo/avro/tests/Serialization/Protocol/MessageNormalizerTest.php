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
use Avro\Model\Protocol\Request;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\RecordField;
use Avro\Model\Schema\Schema;
use Avro\Model\Schema\Union;
use Avro\Serialization\Protocol\MessageNormalizer;
use Avro\Serialization\Schema\Normalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageNormalizerTest extends TestCase
{
    /** @var MessageNormalizer */
    private $normalizer;

    /** @var Normalizer|MockObject */
    private $schemaNormalizer;

    protected function setUp(): void
    {
        $this->schemaNormalizer = $this->createMock(Normalizer::class);
        $this->schemaNormalizer
            ->method('normalize')->willReturn(['PLACEHOLDER']);

        $this->normalizer = new MessageNormalizer($this->schemaNormalizer);
    }

    /**
     * @dataProvider exampleMessages
     *
     * @param Message $message
     * @param array $expected
     */
    public function testNormalize(Message $message, array $expected): void
    {
        $this->assertEquals($expected, $this->normalizer->normalize($message));
    }

    public function exampleMessages(): array
    {
        return [
            [
                Message::oneWay(Name::fromValue('hello'), Request::ofParameters([
                    RecordField::named(Name::fromValue('name'), Primitive::string()),
                ]))->withDoc('Some documentation'),
                [
                    'request' => [['PLACEHOLDER']],
                    'doc' => 'Some documentation',
                    'one-way' => true,
                ],
            ],
            [
                Message::twoWay(
                    Name::fromValue('hello'),
                    Request::ofParameters([
                        RecordField::named(Name::fromValue('firstname'), Primitive::string()),
                        RecordField::named(Name::fromValue('lastname'), Primitive::string()),
                    ]),
                    $this->createMock(Schema::class),
                    Union::of([])
                )->withDoc('Some documentation'),
                [
                    'request' => [['PLACEHOLDER'], ['PLACEHOLDER']],
                    'doc' => 'Some documentation',
                    'one-way' => false,
                    'errors' => ['PLACEHOLDER'],
                    'response' => ['PLACEHOLDER'],
                ],
            ],
        ];
    }
}
