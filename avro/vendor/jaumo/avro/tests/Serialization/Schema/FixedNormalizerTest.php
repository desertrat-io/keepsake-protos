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

use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\LogicalType;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\FixedNormalizer;
use Avro\Serialization\Schema\Normalizer;
use Avro\Serialization\Schema\NormalizerAware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FixedNormalizerTest extends TestCase
{
    /**
     * @var FixedNormalizer
     */
    private $normalizer;

    /**
     * @var Normalizer|MockObject
     */
    private $delegate;

    public function setUp(): void
    {
        $this->normalizer = new FixedNormalizer();
        $this->delegate = $this->createMock(Normalizer::class);
        $this->delegate->method('normalize')->with($this->isInstanceOf(LogicalType::class))->willReturn(['foo' => 'bar']);
        $this->normalizer->setNormalizer($this->delegate);
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Normalizer::class, $this->normalizer);
        $this->assertInstanceOf(NormalizerAware::class, $this->normalizer);
    }

    /**
     * @dataProvider canonicalExamples
     *
     * @param Fixed $schema
     */
    public function testSupporting(Fixed $schema): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($schema));
    }

    public function testNotSupportingNonFixed(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new class() implements Schema {
        }));
    }

    /**
     *  @dataProvider nonCanonicalExamples
     *
     * @param Fixed $schema
     * @param mixed $normalized
     * @throws NormalizationError
     */
    public function testNormalizing(Fixed $schema, $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema));
    }

    /**
     * @throws NormalizationError
     */
    public function testNormalizingWithContextualNamespace(): void
    {
        $this->assertSame(
            [Fixed::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_SIZE => 16],
            $this->normalizer->normalize(
                Fixed::named(NamespacedName::fromValue('com.avro.foo'), 16),
                false,
                (new Context())->withNamespace('com.avro')
            )
        );
        $this->assertSame(
            [Fixed::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_SIZE => 16, Fixed::ATTR_NAMESPACE => 'org.avro'],
            $this->normalizer->normalize(
                Fixed::named(NamespacedName::fromValue('org.avro.foo'), 16),
                false,
                (new Context())->withNamespace('com.avro')
            )
        );
    }

    /**
     *  @dataProvider canonicalExamples
     *
     * @param Fixed $schema
     * @param array $normalized
     * @throws NormalizationError
     */
    public function testCanonicalNormalizing(Fixed $schema, array $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema, true));
    }

    public function nonCanonicalExamples(): array
    {
        return [
            [
                Fixed::named(NamespacedName::fromValue('foo'), 16)->withNamespace('com.avro')->withAliases(['bar', 'baz']),
                [Fixed::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_SIZE => 16, Fixed::ATTR_NAMESPACE => 'com.avro', Fixed::ATTR_ALIASES => ['bar', 'baz']],
            ],
            [
                Fixed::named(NamespacedName::fromValue('foo'), 16),
                [Fixed::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_SIZE => 16],
            ],
            [
                Fixed::duration(NamespacedName::fromValue('foo')),
                [Fixed::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_SIZE => 12, 'foo' => 'bar'],
            ],
        ];
    }

    public function canonicalExamples(): array
    {
        return [
            [
                Fixed::named(NamespacedName::fromValue('foo'), 16)->withNamespace('com.avro')->withAliases(['bar', 'baz']),
                [Fixed::ATTR_NAME => 'com.avro.foo', Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_SIZE => 16],
            ],
            [
                Fixed::duration(NamespacedName::fromValue('foo')),
                [Fixed::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_SIZE => 12],
            ],
        ];
    }
}
