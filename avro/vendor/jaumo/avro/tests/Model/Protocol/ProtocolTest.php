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

use Avro\Model\Protocol\Protocol;
use Avro\Model\Schema\Named;
use Avro\Model\Schema\NamespacedName;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProtocolTest extends TestCase
{
    /** @var Protocol */
    private $protocol;

    protected function setUp(): void
    {
        $this->protocol = Protocol::named(NamespacedName::fromValue('test'));
    }

    public function testProtocolAcceptsOnlyTypesWithoutNamespace(): void
    {
        /** @var Named|MockObject $type */
        $type = $this->createMock(Named::class);
        $type->method('getName')->willReturn('Type');
        $type->method('getNamespace')->willReturn(null);
        $type->method('getFullName')->willReturn('Type');

        /** @var Named|MockObject $typeWithNamespace */
        $typeWithNamespace = $this->createMock(Named::class);
        $typeWithNamespace->method('getName')->willReturn('NamespacedType');
        $typeWithNamespace->method('getNamespace')->willReturn('Namespace');
        $typeWithNamespace->method('getFullName')->willReturn('Namespace.NamespacedType');

        $protocol = $this->protocol->withType($type);
        $this->assertEquals(['Type' => $type], $protocol->getTypes());

        $this->expectException(InvalidArgumentException::class);
        $protocol->withType($typeWithNamespace);
    }
}
