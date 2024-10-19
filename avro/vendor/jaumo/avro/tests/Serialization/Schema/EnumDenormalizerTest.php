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
use Avro\Model\Schema\Named;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Reference;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\Denormalizer;
use Avro\Serialization\Schema\EnumDenormalizer;
use PHPUnit\Framework\TestCase;

final class EnumDenormalizerTest extends TestCase
{
    /** @var EnumDenormalizer */
    private $denormalizer;

    public function setUp(): void
    {
        $this->denormalizer = new EnumDenormalizer();
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Denormalizer::class, $this->denormalizer);
    }

    /**
     * @dataProvider enumTypeExamples
     *
     * @param array $data
     */
    public function testSupporting(array $data): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization($data, Schema::class));
        $this->assertTrue($this->denormalizer->supportsDenormalization($data, Enum::class));
    }

    public function testNotSupporting(): void
    {
        $this->assertFalse($this->denormalizer->supportsDenormalization([], Schema::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Enum::ATTR_TYPE => Primitive::TYPE_STRING], Schema::class));
    }

    /**
     * @dataProvider enumTypeExamples
     *
     * @param array $data
     * @param Named $schema
     * @param string $id
     * @throws DenormalizationError
     */
    public function testDenormalizing(array $data, Named $schema, string $id): void
    {
        $context = new Context();
        $this->assertEquals($schema, $this->denormalizer->denormalize($data, Schema::class, $context));
        $this->assertEquals(Reference::create($schema), $context->getReferenceByName($schema->getFullName()));
    }

    /**
     * @dataProvider namespaceDefinitionExamples
     */
    public function testNamespaceDefinition(array $schema, Context $context, Schema $result): void
    {
        $this->assertEquals(
            $result,
            $this->denormalizer->denormalize($schema, Enum::class, $context)
        );
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithoutName(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([]);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithoutSymbols(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Enum::ATTR_NAME => 'Sign']);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithoutUniqueSymbols(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([
            Enum::ATTR_TYPE => Enum::TYPE,
            Enum::ATTR_NAME => 'Suit',
            Enum::ATTR_SYMBOLS => ['SPADES', 'SPADES', 'DIAMONDS', 'CLUBS'],
        ]);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithoutListOfSymbols(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([
            Enum::ATTR_TYPE => Enum::TYPE,
            Enum::ATTR_NAME => 'Suit',
            Enum::ATTR_SYMBOLS => [4 => 'SPADES', 8 => 'HEARTS', 15 => 'DIAMONDS', 16 => 'CLUBS'],
        ]);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithInvalidName(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Enum::ATTR_TYPE => Enum::TYPE, Enum::ATTR_NAME => '123FOO@bar', Enum::ATTR_SYMBOLS => ['SPADES']]);
    }

    public function enumTypeExamples(): array
    {
        return [
            [
                [Enum::ATTR_TYPE => Enum::TYPE, Enum::ATTR_NAME => 'Suit', Enum::ATTR_SYMBOLS => ['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS']],
                Enum::named(NamespacedName::fromValue('Suit'), $this->castToNames(['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS'])),
                'Suit',
            ],
            [
                [Enum::ATTR_TYPE => Enum::TYPE, Enum::ATTR_NAME => 'Suit', Enum::ATTR_SYMBOLS => ['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS'], Enum::ATTR_NAMESPACE => 'com.avro'],
                Enum::named(NamespacedName::fromValue('Suit'),
                    $this->castToNames(['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS']))->withNamespace('com.avro'),
                'com.avro.Suit',
            ],
            [
                [Enum::ATTR_TYPE => Enum::TYPE, Enum::ATTR_NAME => 'com.avro.Suit', Enum::ATTR_SYMBOLS => ['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS']],
                Enum::named(NamespacedName::fromValue('com.avro.Suit'), $this->castToNames(['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS'])),
                'com.avro.Suit',
            ],
            [
                [Enum::ATTR_TYPE => Enum::TYPE, Enum::ATTR_NAME => 'Suit', Enum::ATTR_SYMBOLS => ['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS'], Enum::ATTR_ALIASES => ['Sign']],
                Enum::named(NamespacedName::fromValue('Suit'),
                    $this->castToNames(['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS']))->withAliases(['Sign']),
                'Suit',
            ],
            [
                [Enum::ATTR_TYPE => Enum::TYPE, Enum::ATTR_NAME => 'Suit', Enum::ATTR_SYMBOLS => ['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS'], Enum::ATTR_DOC => 'The card type'],
                Enum::named(NamespacedName::fromValue('Suit'),
                    $this->castToNames(['SPADES', 'HEARTS', 'DIAMONDS', 'CLUBS']))->withDoc('The card type'),
                'Suit',
            ],
        ];
    }

    public function namespaceDefinitionExamples(): array
    {
        return [
            [
                [Enum::ATTR_NAME => 'Suit', Enum::ATTR_SYMBOLS => []],
                new Context(),
                Enum::named(NamespacedName::fromValue('Suit'), []),
            ],
            [
                [Enum::ATTR_NAME => 'Suit', Enum::ATTR_SYMBOLS => []],
                (new Context())->withNamespace('com.context.avro'),
                Enum::named(NamespacedName::fromValue('com.context.avro.Suit'), []),
            ],
            [
                [Enum::ATTR_NAME => 'Suit', Enum::ATTR_SYMBOLS => [], Enum::ATTR_NAMESPACE => 'com.avro'],
                new Context(),
                Enum::named(NamespacedName::fromValue('com.avro.Suit'), []),
            ],
            [
                [Enum::ATTR_NAME => 'Suit', Enum::ATTR_SYMBOLS => [], Enum::ATTR_NAMESPACE => 'com.avro'],
                (new Context())->withNamespace('com.context.avro'),
                Enum::named(NamespacedName::fromValue('com.avro.Suit'), []),
            ],
            [
                [Enum::ATTR_NAME => 'com.avro.Suit', Enum::ATTR_SYMBOLS => []],
                new Context(),
                Enum::named(NamespacedName::fromValue('com.avro.Suit'), []),
            ],
            [
                [Enum::ATTR_NAME => 'com.avro.Suit', Enum::ATTR_SYMBOLS => []],
                (new Context())->withNamespace('com.context.avro'),
                Enum::named(NamespacedName::fromValue('com.avro.Suit'), []),
            ],
            [
                [Enum::ATTR_NAME => 'com.avro.Suit', Enum::ATTR_SYMBOLS => [], Enum::ATTR_NAMESPACE => 'org.avro'],
                new Context(),
                Enum::named(NamespacedName::fromValue('com.avro.Suit'), []),
            ],
            [
                [Enum::ATTR_NAME => 'com.avro.Suit', Enum::ATTR_SYMBOLS => [], Enum::ATTR_NAMESPACE => 'org.avro'],
                (new Context())->withNamespace('com.context.avro'),
                Enum::named(NamespacedName::fromValue('com.avro.Suit'), []),
            ],
        ];
    }

    private function castToNames(array $symbols): array
    {
        return \array_map(function (string $symbol): Name {
            return Name::fromValue($symbol);
        }, $symbols);
    }
}
