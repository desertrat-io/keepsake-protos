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

namespace Avro\Serialization\Message\BinaryEncoding\Util;

use Avro\AvroException;
use Avro\Model\Schema\Schema;
use Avro\Serialization\Message\BinaryEncoding\BinaryEncoding;
use Avro\Serialization\Message\BinaryEncoding\ByteReader;
use Avro\Serialization\Message\BinaryEncoding\PrimitiveEncoding;

class BlockEncoding
{
    public static function encode(array $items, int $maxBlockSize = 0): string
    {
        $result = '';

        // Auto-expand block-size=0 to all items, but make sure it's at least 1
        $maxBlockSize = \max(1, $maxBlockSize === 0 ? \count($items) : $maxBlockSize);
        $blocks = \array_chunk($items, $maxBlockSize, false);

        foreach ($blocks as $block) {
            $blockItemCount = \count($block);
            $blockData = \implode('', $block);
            $blockSize = \strlen($blockData);

            // A negative item-counts allows for block-size reporting next
            $result .= PrimitiveEncoding::encodeLongOrInt(-1 * $blockItemCount)
                . PrimitiveEncoding::encodeLongOrInt($blockSize)
                . $blockData;
        }

        $result .= PrimitiveEncoding::encodeLongOrInt(0);

        return $result;
    }

    /**
     * @param Schema $schema
     * @param ByteReader $reader
     * @return array
     * @throws AvroException
     */
    public static function decode(Schema $schema, ByteReader $reader): array
    {
        $result = [];

        while (($itemCount = PrimitiveEncoding::decodeLongOrInt($reader)) !== 0) {
            // We don't want to skip blocks, therefore we are not interested in
            // the byte-size of the block and skip it
            if ($itemCount < 0) {
                PrimitiveEncoding::decodeLongOrInt($reader);
                $itemCount = \abs($itemCount);
            }

            for ($i = 0; $i < $itemCount; $i++) {
                $result[] = BinaryEncoding::decode($schema, $reader);
            }
        }

        return $result;
    }
}
