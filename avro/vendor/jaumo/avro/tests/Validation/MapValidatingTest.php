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

namespace AvroTest\Validation;

use Avro\Model\Schema\Map;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Validation\MapValidating;
use PHPUnit\Framework\TestCase;

final class MapValidatingTest extends TestCase
{
    public function testValidArrayMap(): void
    {
        $this->assertTrue(
            MapValidating::isValid(
                ['foo' => 4, 'bar' => 8],
                Map::to(Primitive::int())
            )
        );
    }

    public function testInvalidArrayMap(): void
    {
        $this->assertFalse(
            MapValidating::isValid(
                ['foo' => '4', 'bar' => 8],
                Map::to(Primitive::int())
            )
        );
    }

    public function testNonArrayValue(): void
    {
        $this->assertFalse(
            MapValidating::isValid(
                'foo',
                Map::to(Primitive::int())
            )
        );
    }

    public function testNonStringKeysMapDefault(): void
    {
        $this->assertFalse(
            MapValidating::isValid(
                [42 => 4],
                Map::to(Primitive::int())
            )
        );
    }

    public function testNonMapSchema(): void
    {
        $this->expectException(\TypeError::class);

        MapValidating::isValid(
            ['foo' => 4, 'bar' => 8],
            new class() implements Schema {
            }
        );
    }
}
