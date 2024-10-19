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

use Avro\Model\Schema\Named;
use Avro\Model\Schema\Reference;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\Normalizer;
use Avro\Serialization\Schema\ReferenceNormalizer;
use PHPUnit\Framework\TestCase;

final class ReferenceNormalizerTest extends TestCase
{
    /** @var ReferenceNormalizer */
    private $normalizer;

    public function setUp(): void
    {
        $this->normalizer = new ReferenceNormalizer();
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Normalizer::class, $this->normalizer);
    }

    public function testSupporting(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($this->createReferenceSchema()));
    }

    public function testNotSupportingNonRecord(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new class() implements Schema {
        }));
    }

    /**
     * @throws NormalizationError
     */
    public function testNormalizing(): void
    {
        $this->assertSame('foo', $this->normalizer->normalize($this->createReferenceSchema()));
    }

    /**
     * @throws NormalizationError
     */
    public function testCanonicalNormalizing(): void
    {
        $this->assertSame('com.avro.foo', $this->normalizer->normalize($this->createReferenceSchema(), true));
    }

    /**
     * @throws NormalizationError
     */
    public function testNormalizingWithContextualNamespace(): void
    {
        $this->assertSame(
            'foo',
            $this->normalizer->normalize(
                $this->createReferenceSchema(),
                false,
                (new Context())->withNamespace('com.avro')
            )
        );

        $this->assertSame(
            'com.avro.foo',
            $this->normalizer->normalize(
                $this->createReferenceSchema(),
                false,
                (new Context())->withNamespace('org.avro')
            )
        );
    }

    private function createReferenceSchema(): Reference
    {
        return Reference::create(new class() implements Named {
            public function getName(): string
            {
                return 'foo';
            }

            public function getNamespace(): ?string
            {
                return 'com.avro';
            }

            public function getFullName(): string
            {
                return 'com.avro.foo';
            }
        });
    }
}
