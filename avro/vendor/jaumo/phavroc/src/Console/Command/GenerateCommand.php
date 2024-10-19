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

namespace Phavroc\Console\Command;

use League\Flysystem\FileExistsException;
use Phavroc\Avro\Loader\Loader;
use Phavroc\Avro\Transpiling\Package;
use Phavroc\Avro\Transpiling\TranspileError;
use Phavroc\Avro\Transpiling\Transpiler;
use Phavroc\FileWriter\FileWriter;
use Phavroc\PhpDumper\DeprecationMap;
use Phavroc\PhpDumper\PhpDumper;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateCommand extends Command
{
    private Loader $loader;
    private Transpiler $transpiler;
    private PhpDumper $dumper;
    private FileWriter $writer;

    public function __construct(
        Loader $loader,
        Transpiler $transpiler,
        PhpDumper $dumper,
        FileWriter $writer
    ) {
        parent::__construct();

        $this->loader = $loader;
        $this->transpiler = $transpiler;
        $this->dumper = $dumper;
        $this->writer = $writer;
    }

    protected function configure(): void
    {
        $this
            ->setName('generate')
            ->addArgument('input-dir', InputArgument::REQUIRED, 'input schema or directory containing schemas')
            ->addArgument('output-dir', InputArgument::OPTIONAL, 'directory to write generated php', 'build')
            ->addOption('common-interface', null, InputOption::VALUE_REQUIRED, 'The FQCN of the common interface', PhpDumper::DEFAULT_INTERFACE_NAME)
            ->addOption('deprecation-map', null, InputOption::VALUE_REQUIRED, 'Location of the deprecation-mapping.json file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        if ($application === null) {
            throw new \RuntimeException('Command not registered on the application');
        }
        $output->writeln(sprintf('%s <info>%s</info>', $application->getName(), $application->getVersion()));

        $inputDir = $input->getArgument('input-dir') ?? '';
        if (\is_array($inputDir)) {
            $inputDir = $inputDir[0] ?? '';
        }

        // Configure output dir
        $outputDir = $input->getArgument('output-dir');
        if (\is_array($outputDir)) {
            $outputDir = $outputDir[0] ?? '';
        }
        $this->writer->setBaseDir($outputDir);

        $deprecationMapPath = $input->getOption('deprecation-map');
        $deprecationMap = $deprecationMapPath !== null ? new DeprecationMap($deprecationMapPath) : null;

        // Load information
        $package = new Package();
        $package = $this->configureInterface($package, $input);
        $package = $this->load($package, $inputDir);

        $this->showInfo($package, $inputDir, $outputDir, $deprecationMapPath, $output);

        $this->writeInterfaces($output);
        $this->writeClasses($package, $deprecationMap, $output);

        return 0;
    }

    private function showInfo(Package $package, string $inputDir, ?string $outputDir, ?string $deprecationMap, OutputInterface $output): void
    {
        $output->writeln('');

        (new Table($output))
            ->setStyle('compact')
            ->setRows([
                ['<comment>Input directory</comment>', $inputDir],
                ['<comment>Output directory</comment>', $outputDir ?? '(null)'],
                ['<comment>Common interface</comment>', $package->commonInterface()],
                ['<comment>Deprecation mapping</comment>', $deprecationMap ?? '(null)'],
            ])
            ->render();

        $output->writeln('');
    }

    private function configureInterface(Package $package, InputInterface $input): Package
    {
        // Every class is expected to implement the default interface
        if (!$input->hasOption('common-interface')) {
            return $package->withCommonInterface(PhpDumper::DEFAULT_INTERFACE_NAME);
        }

        $commonInterface = $input->getOption('common-interface') ?? '';
        if (!\is_string($commonInterface) || $commonInterface === '') {
            throw new RuntimeException('--common-interface has to be a non-empty string');
        }

        return $package->withCommonInterface(ltrim($commonInterface, '\\'));
    }

    private function load(Package $package, string $inputDir): Package
    {
        foreach ($this->loader->load($inputDir) as $schema) {
            try {
                $package = $this->transpiler->transpile($schema, $package);
            } catch (TranspileError $e) {
                throw new \RuntimeException(sprintf(
                    'Error while transpiling schema: %s',
                    $e->getMessage()
                ));
            }
        }

        return $package;
    }

    private function writeInterfaces(OutputInterface $output): void
    {
        // Always dump the default interface
        $this->write(PhpDumper::DEFAULT_INTERFACE_NAME, $this->dumper->dumpDefaultInterface(), $output);
    }

    private function writeClasses(Package $package, ?DeprecationMap $deprecationMap, OutputInterface $output): void
    {
        foreach ($package->classes() as $class) {
            $this->write($class->fqcn(), $this->dumper->dump($class, $deprecationMap), $output);
        }
    }

    private function write(string $name, string $contents, OutputInterface $output): void
    {
        $output->write(sprintf(' %s...', $name));
        try {
            $path = str_replace('\\', '//', $name);

            $this->writer->write(sprintf('%s.php', $path), $contents);
            $output->writeln(' <info>✔</info>');
        } catch (FileExistsException $e) {
            $output->writeln(' <info>skipped (file exists)</info>');
        }
    }
}
