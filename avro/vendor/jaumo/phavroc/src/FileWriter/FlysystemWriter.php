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

namespace Phavroc\FileWriter;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

final class FlysystemWriter implements FileWriter
{
    private FilesystemInterface $fs;

    public function __construct(FilesystemInterface $fs)
    {
        $this->fs = $fs;
    }

    public function write(string $path, string $content): void
    {
        $this->fs->write($path, $content);
    }

    public function setBaseDir(string $baseDir): void
    {
        if (!$this->fs instanceof Filesystem) {
            throw new \RuntimeException(sprintf(
                'Unable to override base directory of "%s" filesystem',
                \get_class($this->fs)
            ));
        }

        $adapter = $this->fs->getAdapter();
        if (!$adapter instanceof AbstractAdapter) {
            throw new \RuntimeException(sprintf(
                'Unable to override base directory of "%s" adapter',
                \get_class($adapter)
            ));
        }

        $adapter->setPathPrefix($baseDir);
    }
}
