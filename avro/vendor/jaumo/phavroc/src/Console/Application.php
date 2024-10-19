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

namespace Phavroc\Console;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Phavroc\Avro\Loader\ChainLoader;
use Phavroc\Avro\Loader\DirectoryLoader;
use Phavroc\Avro\Loader\FileLoader;
use Phavroc\Avro\Transpiling\Transpiler\AvroArraySchema;
use Phavroc\Avro\Transpiling\Transpiler\AvroEnumSchema;
use Phavroc\Avro\Transpiling\Transpiler\AvroField;
use Phavroc\Avro\Transpiling\Transpiler\AvroPrimitiveSchema;
use Phavroc\Avro\Transpiling\Transpiler\AvroRecordSchema;
use Phavroc\Avro\Transpiling\Transpiler\AvroUnionSchema;
use Phavroc\Avro\Transpiling\Transpiler\ChainTranspiler;
use Phavroc\Console\Command\GenerateCommand;
use Phavroc\FileWriter\FlysystemWriter;
use Phavroc\PhpDumper\PhpParser;
use Phavroc\PhpDumper\PhpParser\AvroSchema;
use Phavroc\PhpDumper\PhpParser\DTONormalization;
use Phavroc\PhpDumper\PhpParser\Enum;
use Phavroc\PhpDumper\PhpParser\GetSet;
use Phavroc\PhpDumper\PhpParser\Getter;
use Phavroc\PhpDumper\PhpParser\Setter;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public function __construct(string $version)
    {
        parent::__construct('Phavroc', $version);

        $loader = new ChainLoader();
        $loader->addLoader(new FileLoader());
        $loader->addLoader(new DirectoryLoader());

        $transpiler = new ChainTranspiler();
        $transpiler->addTranspiler(new AvroRecordSchema());
        $transpiler->addTranspiler(new AvroField());
        $transpiler->addTranspiler(new AvroPrimitiveSchema());
        $transpiler->addTranspiler(new AvroUnionSchema());
        $transpiler->addTranspiler(new AvroEnumSchema());
        $transpiler->addTranspiler(new AvroArraySchema());

        $parser = new PhpParser();
        $parser->addNodesProvider(new AvroSchema());
        $getSet = new GetSet(
            [
                new Getter\TimestampMillis(),
                new Getter\TimestampMicros(),
                new Getter\Date(),
                new Getter\Standard(),
            ],
            [
                new Setter\TimestampMillis(),
                new Setter\TimestampMicros(),
                new Setter\Date(),
                new Setter\Standard(),
            ]
        );
        $parser->addNodesProvider($getSet);
        $parser->addNodesProvider(new Enum());
        $parser->addNodesProvider(new DTONormalization());

        $command = new GenerateCommand(
            $loader,
            $transpiler,
            $parser,
            new FlysystemWriter(new Filesystem(new Local('.')))
        );
        $this->add($command);
        $this->setDefaultCommand('generate', true);
    }
}
