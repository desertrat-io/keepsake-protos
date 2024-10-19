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

use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\EnumNormalizer;
use Avro\Serialization\Schema\Normalizer;
use PHPUnit\Framework\TestCase;

final class EnumNormalizerTest extends TestCase
{
    /**
     * @var EnumNormalizer
     */
    private $normalizer;

    public function setUp(): void
    {
        $this->normalizer = new EnumNormalizer();
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Normalizer::class, $this->normalizer);
    }

    /**
     * @dataProvider canonicalExamples
     *
     * @param Enum $schema
     */
    public function testSupporting(Enum $schema): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($schema));
    }

    public function testNotSupportingNonEnum(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new class() implements Schema {
        }));
    }

    /**
     * @dataProvider nonCanonicalExamples
     *
     * @param Enum $schema
     * @param mixed $normalized
     * @throws NormalizationError
     */
    public function testNormalizing(Enum $schema, $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema));
    }

    /**
     * @dataProvider canonicalExamples
     *
     * @param Enum $schema
     * @param array $normalized
     * @throws NormalizationError
     */
    public function testCanonicalNormalizing(Enum $schema, array $normalized): void
    {
        $this->assertSame($normalized, $this->normalizer->normalize($schema, true));
    }

    /**
     * @throws NormalizationError
     */
    public function testNormalizingWithContextualNamespace(): void
    {
        $this->assertSame(
            [Enum::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Enum::TYPE, Enum::ATTR_SYMBOLS => []],
            $this->normalizer->normalize(
                Enum::named(NamespacedName::fromValue('com.avro.foo'), []),
                false,
                (new Context())->withNamespace('com.avro')
            )
        );
        $this->assertSame(
            [Enum::ATTR_NAME => 'foo', Schema::ATTR_TYPE => Enum::TYPE, Enum::ATTR_SYMBOLS => [], Enum::ATTR_NAMESPACE => 'org.avro'],
            $this->normalizer->normalize(
                Enum::named(NamespacedName::fromValue('org.avro.foo'), []),
                false,
                (new Context())->withNamespace('com.avro')
            )
        );
    }

    public function nonCanonicalExamples(): array
    {
        return [
            [
                Enum::named(NamespacedName::fromValue('foo'), [Name::fromValue('AAA'), Name::fromValue('BBB')])
                    ->withNamespace('com.avro')
                    ->withDoc('lorem')
                    ->withAliases(['bar', 'baz']),
                [
                    Enum::ATTR_NAME => 'foo',
                    Schema::ATTR_TYPE => Enum::TYPE,
                    Enum::ATTR_SYMBOLS => ['AAA', 'BBB'],
                    Enum::ATTR_NAMESPACE => 'com.avro',
                    Enum::ATTR_DOC => 'lorem',
                    Enum::ATTR_ALIASES => ['bar', 'baz'],
                ],
            ],
        ];
    }

    public function canonicalExamples(): array
    {
        return [
            [
                Enum::named(NamespacedName::fromValue('foo'), [Name::fromValue('AAA'), Name::fromValue('BBB')])
                    ->withNamespace('com.avro')
                    ->withDoc('lorem')
                    ->withAliases(['bar', 'baz']),
                [
                    Enum::ATTR_NAME => 'com.avro.foo',
                    Schema::ATTR_TYPE => Enum::TYPE,
                    Enum::ATTR_SYMBOLS => ['AAA', 'BBB'],
                ],
            ],
        ];
    }
}
