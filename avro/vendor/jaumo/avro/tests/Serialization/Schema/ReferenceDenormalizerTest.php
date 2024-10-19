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

use Avro\Model\Schema\Array_;
use Avro\Model\Schema\Enum;
use Avro\Model\Schema\Fixed;
use Avro\Model\Schema\Map;
use Avro\Model\Schema\Name;
use Avro\Model\Schema\NamespacedName;
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Record;
use Avro\Model\Schema\Reference;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Context;
use Avro\Serialization\DenormalizationError;
use Avro\Serialization\Schema\Denormalizer;
use Avro\Serialization\Schema\ReferenceDenormalizer;
use PHPUnit\Framework\TestCase;

final class ReferenceDenormalizerTest extends TestCase
{
    /** @var ReferenceDenormalizer */
    private $denormalizer;

    public function setUp(): void
    {
        $this->denormalizer = new ReferenceDenormalizer();
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Denormalizer::class, $this->denormalizer);
    }

    public function testSupporting(): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => 'com.avro.Bar'], Schema::class));
        $this->assertTrue($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => 'Bar'], Schema::class));

        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => 'com.avro.Bar'], Record::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => 'Bar'], Record::class));

        foreach (Primitive::TYPES as $type) {
            $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => $type], Schema::class));
        }

        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Array_::TYPE], Schema::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Enum::TYPE], Schema::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Fixed::TYPE], Schema::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Map::TYPE], Schema::class));
        $this->assertFalse($this->denormalizer->supportsDenormalization([Schema::ATTR_TYPE => Record::TYPE], Schema::class));
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingAvailableReferenceWithFullName(): void
    {
        $context = new Context();

        $schema = Record::named(NamespacedName::fromValue('com.avro.Bar'));
        $context->createReference($schema);

        $this->assertEquals(
            Reference::create($schema),
            $this->denormalizer->denormalize([Schema::ATTR_TYPE => 'com.avro.Bar'], Schema::class, $context)
        );
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingAvailableReferenceWithName(): void
    {
        $context = new Context();

        $schema = Record::named(NamespacedName::fromValue('Bar'));
        $context->createReference($schema);

        $this->assertEquals(
            Reference::create($schema),
            $this->denormalizer->denormalize([Schema::ATTR_TYPE => 'Bar'], Schema::class, $context)
        );
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingNonAvailableReferenceWithNameAndContextualNamespace(): void
    {
        $context = (new Context())->withNamespace('com.avro');

        $schema = Record::named(NamespacedName::fromName(Name::fromValue('Bar'), 'com.avro'));
        $context->createReference($schema);

        $this->assertEquals(
            Reference::create($schema),
            $this->denormalizer->denormalize([Schema::ATTR_TYPE => 'Bar'], Schema::class, $context)
        );
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingNonAvailableReferenceWithFullName(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Schema::ATTR_TYPE => 'com.avro.Bar']);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingNonAvailableReferenceWithName(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Schema::ATTR_TYPE => 'Bar']);
    }

    /**
     * @throws DenormalizationError
     */
    public function testDenormalizingNonAvailableReferenceWithNameAndEmptyContext(): void
    {
        $this->expectException(DenormalizationError::class);

        $this->denormalizer->denormalize([Schema::ATTR_TYPE => 'Bar'], Schema::class, new Context());
    }
}
