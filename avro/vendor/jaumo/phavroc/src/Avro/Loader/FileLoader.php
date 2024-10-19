<?php

/*
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

namespace Phavroc\Avro\Loader;

use Avro\AvroException;

final class FileLoader implements Loader
{
    public function supports(string $from): bool
    {
        return is_file($from) && Loader::AVRO_SCHEMA_EXTENSION === pathinfo($from, PATHINFO_EXTENSION);
    }

    public function load(string $from): array
    {
        if (false === $content = file_get_contents($from)) {
            throw LoaderError::cannotLoadFile($from);
        }

        try {
            return [\Avro\Serde::parseSchema((string) $content)];
        } catch (AvroException $e) {
            throw LoaderError::cannotLoadFile($from, $e->getMessage());
        }
    }
}
