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

namespace AvroTest\Model\Protocol;

use Avro\Model\Protocol\Message;
use Avro\Model\Protocol\Request;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /** @var Message */
    private $oneWayMessage;

    /** @var Message */
    private $twoWayMessage;

    protected function setUp(): void
    {
        $messageName = Name::fromValue('Test');
        $request = Request::ofParameters([]);

        /** @var MockObject|Schema $response */
        $response = $this->createMock(Schema::class);

        $this->oneWayMessage = Message::oneWay($messageName, $request);
        $this->twoWayMessage = Message::twoWay($messageName, $request, $response);
    }

    public function testOneWayDetection(): void
    {
        $this->assertTrue($this->oneWayMessage->isOneWay());
        $this->assertFalse($this->twoWayMessage->isOneWay());
    }

    public function testEffectiveErrors(): void
    {
        $errorTypes = $this->oneWayMessage->getEffectiveErrors()->getTypes();
        $this->assertGreaterThan(0, \count($errorTypes));

        $firstType = $errorTypes[0];
        $this->assertEquals(Primitive::string(), $firstType);
    }
}
