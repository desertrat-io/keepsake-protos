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

use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Union;
use Avro\Validation\UnionValidating;
use PHPUnit\Framework\TestCase;

final class UnionValidatingTest extends TestCase
{
    public function testValidValue(): void
    {
        $this->assertTrue(
            UnionValidating::isValid(
                null,
                Union::of([
                    Primitive::null(),
                    Primitive::string(),
                ])
            )
        );
    }

    public function testInvalidVaue(): void
    {
        $this->assertFalse(
            UnionValidating::isValid(
                42,
                Union::of([
                    Primitive::null(),
                    Primitive::string(),
                ])
            )
        );
    }

    public function testNonUnionSchema(): void
    {
        $this->expectException(\TypeError::class);
        UnionValidating::isValid(
            'foo',
            Primitive::string()
        );
    }
}
