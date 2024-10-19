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
use Avro\Model\Schema\Named;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Reference;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\Denormalizer;
use Avro\Serialization\Schema\FixedDenormalizer;
use PHPUnit\Framework\TestCase;

final class FixedDenormalizerTest extends TestCase
{
    /** @var FixedDenormalizer */
    private $denormalizer;

    public function setUp(): void
    {
        $this->denormalizer = new FixedDenormalizer();
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Denormalizer::class, $this->denormalizer);
    }

    public function testSupporting(): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization([Fixed::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => 16], Fixed::class));
        $this->assertTrue($this->denormalizer->supportsDenormalization([Fixed::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => 16], Fixed::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Fixed::ATTR_TYPE => Primitive::TYPE_INT], Fixed::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Fixed::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => 16], Primitive::class));
    }

    /**
     * @dataProvider fixedTypeExamples
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
            $this->denormalizer->denormalize($schema, Fixed::class, $context)
        );
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithoutName(): void
    {
        $this->expectException(DenormalizationError::class);
        $this->denormalizer->denormalize([Fixed::ATTR_TYPE => Fixed::TYPE]);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithoutSize(): void
    {
        $this->expectException(DenormalizationError::class);
        $this->denormalizer->denormalize([Fixed::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'md5']);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithInvalidSize(): void
    {
        $this->expectException(DenormalizationError::class);
        $this->denormalizer->denormalize([Fixed::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => '16']);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingWithInvalidName(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Fixed::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => '123FOO@bar', Fixed::ATTR_SIZE => 16]);
    }

    public function fixedTypeExamples(): array
    {
        return [
            [[Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => 16], Fixed::named(NamespacedName::fromValue('md5'), 16), 'md5'],
            [
                [
                    Schema::ATTR_TYPE => Fixed::TYPE,
                    Fixed::ATTR_NAME => 'md5',
                    Fixed::ATTR_SIZE => 16,
                    Fixed::ATTR_NAMESPACE => 'com.avro',
                ],
                Fixed::named(NamespacedName::fromValue('md5'), 16)->withNamespace('com.avro'),
                'com.avro.md5',
            ],
            [[Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'com.avro.md5', Fixed::ATTR_SIZE => 16], Fixed::named(NamespacedName::fromValue('com.avro.md5'), 16), 'com.avro.md5'],
            [
                [
                    Schema::ATTR_TYPE => Fixed::TYPE,
                    Fixed::ATTR_NAME => 'md5',
                    Fixed::ATTR_SIZE => 16,
                    Fixed::ATTR_ALIASES => ['hash'],
                ],
                Fixed::named(NamespacedName::fromValue('md5'), 16)->withAliases(['hash']),
                'md5',
            ],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_DURATION, Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'period'], Fixed::duration(NamespacedName::fromValue('period')), 'period'],
            [[Schema::ATTR_LOGICAL_TYPE => Schema::LOGICAL_TYPE_DATE, Schema::ATTR_TYPE => Fixed::TYPE, Fixed::ATTR_NAME => 'period', Fixed::ATTR_SIZE => 16], Fixed::named(NamespacedName::fromValue('period'), 16), 'period'],
        ];
    }

    public function namespaceDefinitionExamples(): array
    {
        return [
            [
                [Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => 16],
                new Context(),
                Fixed::named(NamespacedName::fromValue('md5'), 16),
            ],
            [
                [Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => 16],
                (new Context())->withNamespace('com.context.avro'),
                Fixed::named(NamespacedName::fromValue('com.context.avro.md5'), 16),
            ],
            [
                [Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => 16, Fixed::ATTR_NAMESPACE => 'com.avro'],
                new Context(),
                Fixed::named(NamespacedName::fromValue('com.avro.md5'), 16),
            ],
            [
                [Fixed::ATTR_NAME => 'md5', Fixed::ATTR_SIZE => 16, Fixed::ATTR_NAMESPACE => 'com.avro'],
                (new Context())->withNamespace('com.context.avro'),
                Fixed::named(NamespacedName::fromValue('com.avro.md5'), 16),
            ],
            [
                [Fixed::ATTR_NAME => 'com.avro.md5', Fixed::ATTR_SIZE => 16],
                new Context(),
                Fixed::named(NamespacedName::fromValue('com.avro.md5'), 16),
            ],
            [
                [Fixed::ATTR_NAME => 'com.avro.md5', Fixed::ATTR_SIZE => 16],
                (new Context())->withNamespace('com.context.avro'),
                Fixed::named(NamespacedName::fromValue('com.avro.md5'), 16),
            ],
            [
                [Fixed::ATTR_NAME => 'com.avro.md5', Fixed::ATTR_SIZE => 16, Fixed::ATTR_NAMESPACE => 'org.avro'],
                new Context(),
                Fixed::named(NamespacedName::fromValue('com.avro.md5'), 16),
            ],
            [
                [Fixed::ATTR_NAME => 'com.avro.md5', Fixed::ATTR_SIZE => 16, Fixed::ATTR_NAMESPACE => 'org.avro'],
                (new Context())->withNamespace('com.context.avro'),
                Fixed::named(NamespacedName::fromValue('com.avro.md5'), 16),
            ],
        ];
    }
}
