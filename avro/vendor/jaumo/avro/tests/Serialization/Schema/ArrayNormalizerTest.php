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
use Avro\Model\Schema\Primitive;
use Avro\Model\Schema\Schema;
use Avro\Serialization\NormalizationError;
use Avro\Serialization\Schema\ArrayNormalizer;
use Avro\Serialization\Schema\Normalizer;
use Avro\Serialization\Schema\NormalizerAware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ArrayNormalizerTest extends TestCase
{
    /**
     * @var ArrayNormalizer
     */
    private $normalizer;

    /**
     * @var Normalizer|MockObject
     */
    private $delegate;

    public function setUp(): void
    {
        $this->normalizer = new ArrayNormalizer();
        $this->delegate = $this->createMock(Normalizer::class);
        $this->delegate
             ->method('normalize')
             ->will($this->returnCallback(function (Schema $schema, bool $canonical) {
                 $classname = \substr(\get_class($schema), \strrpos(\get_class($schema), '\\') + 1);
                 if ($canonical) {
                     return \sprintf('*%s-canonical-data*', $classname);
                 }

                 return \sprintf('*%s-data*', $classname);
             }));
        $this->normalizer->setNormalizer($this->delegate);
    }

    public function testType(): void
    {
        $this->assertInstanceOf(Normalizer::class, $this->normalizer);
        $this->assertInstanceOf(NormalizerAware::class, $this->normalizer);
    }

    public function testSupporting(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization(Array_::of(new class() implements Schema {
        })));
    }

    public function testNotSupportingNonArray(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new class() implements Schema {
        }));
    }

    /**
     * @throws NormalizationError
     */
    public function testNormalizing(): void
    {
        $this->assertSame(
            [Schema::ATTR_TYPE => Array_::TYPE, Array_::ATTR_ITEMS => '*Primitive-data*'],
            $this->normalizer->normalize(Array_::of(Primitive::string()))
        );
    }

    /**
     * @throws NormalizationError
     */
    public function testCanonicalNormalizing(): void
    {
        $this->assertSame(
            [Schema::ATTR_TYPE => Array_::TYPE, Array_::ATTR_ITEMS => '*Primitive-canonical-data*'],
            $this->normalizer->normalize(Array_::of(Primitive::string()), true)
        );
    }
}
