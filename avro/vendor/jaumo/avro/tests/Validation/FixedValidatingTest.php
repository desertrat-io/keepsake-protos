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

use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Schema;
use Avro\Validation\FixedValidating;
use PHPUnit\Framework\TestCase;

final class FixedValidatingTest extends TestCase
{
    public function testValidValue(): void
    {
        $this->assertTrue(
            FixedValidating::isValid(
                'abcdef',
                Fixed::named(NamespacedName::fromValue('custom'), 6)
            )
        );
    }

    public function testInvalidDefault(): void
    {
        $this->assertFalse(
            FixedValidating::isValid(
                'abcdef',
                Fixed::named(NamespacedName::fromValue('custom'), 24)
            )
        );
    }

    public function testNonStringDefault(): void
    {
        $this->assertFalse(
            FixedValidating::isValid(
                42,
                Fixed::named(NamespacedName::fromValue('custom'), 6)
            )
        );
    }

    public function testNonFixedSchema(): void
    {
        $this->expectException(\TypeError::class);
        FixedValidating::isValid(
            'abcdef',
            new class() implements Schema {
            }
        );
    }
}
