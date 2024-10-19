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

use Avro\Model\Schema\Array_;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Validation\ArrayValidating;
use PHPUnit\Framework\TestCase;

final class ArrayValidatingTest extends TestCase
{
    public function testValid(): void
    {
        $this->assertTrue(
            ArrayValidating::isValid(
                [4, 8, 15],
                Array_::of(Primitive::int())
            )
        );
    }

    public function testInvalid(): void
    {
        $this->assertFalse(
            ArrayValidating::isValid(
                [4, 'foo', 15],
                Array_::of(Primitive::int())
            )
        );
    }

    public function testNonArray(): void
    {
        $this->assertFalse(
            ArrayValidating::isValid(
                'foo',
                Array_::of(new class() implements Schema {
                })
            )
        );
    }

    public function testNonArraySchema(): void
    {
        $this->expectException(\TypeError::class);
        ArrayValidating::isValid(
            [4, 8, 15],
            new class() implements Schema {
            }
        );
    }
}
