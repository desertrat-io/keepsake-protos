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
use Avro\Model\Schema\Schema;
use Avro\Validation\PrimitiveValidating;
use PHPUnit\Framework\TestCase;

final class PrimitiveValidatingTest extends TestCase
{
    /**
     * @dataProvider examples
     */
    public function testValidation($schema, $value, $expected): void
    {
        $this->assertSame(
            $expected,
            PrimitiveValidating::isValid($value, $schema)
        );
    }

    public function testNonPrimitiveSchema(): void
    {
        $this->expectException(\TypeError::class);

        PrimitiveValidating::isValid(
            'foo',
            new class() implements Schema {
            }
        );
    }

    public function testOufOfBoundInt(): void
    {
        $this->assertFalse(
            PrimitiveValidating::isValid(
                PrimitiveValidating::MIN_INT_VALUE - 1,
                Primitive::int()
            )
        );
        $this->assertFalse(
            PrimitiveValidating::isValid(
                PrimitiveValidating::MAX_INT_VALUE + 1,
                Primitive::int()
            )
        );
    }

    public function testOufOfBoundLong(): void
    {
        $this->assertFalse(
            PrimitiveValidating::isValid(
                PrimitiveValidating::MIN_LONG_VALUE - 1,
                Primitive::long()
            )
        );
        $this->assertFalse(
            PrimitiveValidating::isValid(
                PrimitiveValidating::MAX_LONG_VALUE + 1,
                Primitive::long()
            )
        );
    }

    public function examples(): array
    {
        return [
            [Primitive::null(), 42, false],
            [Primitive::null(), M_PI, false],
            [Primitive::null(), 'foo', false],
            [Primitive::null(), true, false],
            [Primitive::null(), false, false],
            [Primitive::null(), [], false],
            [Primitive::null(), new \stdClass(), false],
            [Primitive::null(), null, true],

            [Primitive::boolean(), 42, false],
            [Primitive::boolean(), M_PI, false],
            [Primitive::boolean(), 'foo', false],
            [Primitive::boolean(), true, true],
            [Primitive::boolean(), false, true],
            [Primitive::boolean(), [], false],
            [Primitive::boolean(), new \stdClass(), false],
            [Primitive::boolean(), null, false],

            [Primitive::int(), 42, true],
            [Primitive::int(), M_PI, false],
            [Primitive::int(), 'foo', false],
            [Primitive::int(), true, false],
            [Primitive::int(), false, false],
            [Primitive::int(), [], false],
            [Primitive::int(), new \stdClass(), false],
            [Primitive::int(), null, false],

            [Primitive::long(), 42, true],
            [Primitive::long(), M_PI, false],
            [Primitive::long(), 'foo', false],
            [Primitive::long(), true, false],
            [Primitive::long(), false, false],
            [Primitive::long(), [], false],
            [Primitive::long(), new \stdClass(), false],
            [Primitive::long(), null, false],

            [Primitive::float(), 42, true],
            [Primitive::float(), M_PI, true],
            [Primitive::float(), 'foo', false],
            [Primitive::float(), true, false],
            [Primitive::float(), false, false],
            [Primitive::float(), [], false],
            [Primitive::float(), new \stdClass(), false],
            [Primitive::float(), null, false],

            [Primitive::double(), 42, true],
            [Primitive::double(), M_PI, true],
            [Primitive::double(), 'foo', false],
            [Primitive::double(), true, false],
            [Primitive::double(), false, false],
            [Primitive::double(), [], false],
            [Primitive::double(), new \stdClass(), false],
            [Primitive::double(), null, false],

            [Primitive::bytes(), 42, false],
            [Primitive::bytes(), M_PI, false],
            [Primitive::bytes(), 'foo', true],
            [Primitive::bytes(), true, false],
            [Primitive::bytes(), false, false],
            [Primitive::bytes(), [], false],
            [Primitive::bytes(), new \stdClass(), false],
            [Primitive::bytes(), null, false],

            [Primitive::string(), 42, false],
            [Primitive::string(), M_PI, false],
            [Primitive::string(), 'foo', true],
            [Primitive::string(), true, false],
            [Primitive::string(), false, false],
            [Primitive::string(), [], false],
            [Primitive::string(), new \stdClass(), false],
            [Primitive::string(), null, false],

        ];
    }
}
