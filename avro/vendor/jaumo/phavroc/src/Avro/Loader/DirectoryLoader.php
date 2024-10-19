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
use Avro\Model\Schema\Schema;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class DirectoryLoader implements Loader
{
    public function supports(string $from): bool
    {
        return is_dir($from);
    }

    public function load(string $from): array
    {
        $finder = new Finder();
        $finder->files()->name(sprintf('*.%s', Loader::AVRO_SCHEMA_EXTENSION))->in($from);

        return array_map(
            function (SplFileInfo $file): Schema {
                try {
                    return \Avro\Serde::parseSchema($file->getContents());
                } catch (AvroException $e) {
                    throw LoaderError::cannotLoadFile($file->getPathname(), $e->getMessage());
                } catch (\Exception $e) {
                    throw LoaderError::cannotLoadFile($file->getPathname());
                }
            },
            iterator_to_array($finder)
        );
    }
}
