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

namespace AvroTest\Serialization\Schema;

use Avro\AvroException;
use Avro\Model\Schema\LogicalType;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Schema\LogicalTypeNormalizer;
use Avro\Serialization\Schema\Normalizer;
use PHPUnit\Framework\TestCase;

final class LogicalTypeNormalizerTest extends TestCase
{
    /**
     * @var LogicalTypeNormalizer
     */
    private $normalizer;

    public function setUp(): void
    {
        $this->normalizer = new LogicalTypeNormalizer();
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Normalizer::class, $this->normalizer);
    }

    public function testSupporting(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization(LogicalType::named('foo')));
    }

    public function testNotSupportingNonLogicalType(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new class() implements Schema {
        }));
    }

    /**
     * @throws AvroException
     */
    public function testNormalizing(): void
    {
        $this->assertSame(
            [Schema::ATTR_LOGICAL_TYPE => 'foo'],
            $this->normalizer->normalize(LogicalType::named('foo'))
        );
        $this->assertSame(
            [Schema::ATTR_LOGICAL_TYPE => 'foo', 'bar' => 'baz'],
            $this->normalizer->normalize(LogicalType::named('foo', ['bar' => 'baz']))
        );
    }

    /**
     * @throws AvroException
     */
    public function testCanonicalNormalizing(): void
    {
        $this->assertEmpty($this->normalizer->normalize(LogicalType::named('foo'), true));
    }
}
