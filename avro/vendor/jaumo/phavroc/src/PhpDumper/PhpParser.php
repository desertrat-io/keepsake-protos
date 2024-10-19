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

namespace Phavroc\PhpDumper;

use Phavroc\Avro\Transpiling\Class_ as TranspiledClass;
use Phavroc\Avro\Transpiling\Enum as EnumClass;
use Phavroc\PhpDumper\PhpParser\NodesProvider;
use PhpParser\Builder\Class_;
use PhpParser\Builder\Enum_;
use PhpParser\Builder\Namespace_;
use PhpParser\Comment\Doc;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\PrettyPrinter\Standard;

final class PhpParser implements PhpDumper
{
    /** @var NodesProvider[] */
    private array $providers = [];

    public function addNodesProvider(NodesProvider $provider): void
    {
        $this->providers[] = $provider;
    }

    public function dump(TranspiledClass $transpiled, ?DeprecationMap $deprecationMap): string
    {
        $namespace = new Namespace_($transpiled->namespace());
        if ($transpiled instanceof EnumClass) {
            $class = new Enum_(ucfirst($transpiled->name()));
            $class->setScalarType('int');
        } else {
            $class = new Class_(ucfirst($transpiled->name()));
            $class->makeFinal();
        }

        $schemaDeprecationDoc = $deprecationMap?->getSchemaDeprecation($transpiled->fqcn());
        if (null !== $transpiled->doc() || null !== $schemaDeprecationDoc) {
            $deprecationLine = $schemaDeprecationDoc !== null ? " * @deprecated $schemaDeprecationDoc\n" : '';
            $docLine = $transpiled->doc() !== null ? " * {$transpiled->doc()}\n" : '';
            $class->setDocComment(sprintf("/**\n%s%s */", $docLine, $deprecationLine));
        }

        if (null !== $interface = $transpiled->interface()) {
            $class->implement('\\' . $interface);
        }

        foreach ($this->providers as $provider) {
            if ($provider->supports($transpiled)) {
                $class->addStmts($provider->getNodes($transpiled, $deprecationMap));
            }
        }

        $namespace->addStmt($class);

        $ast = [
            new Declare_(
                [
                    new DeclareDeclare('strict_types', new LNumber(1)),
                ],
                null,
                ['comments' => [new Doc("/**\n * Autogenerated by jaumo/phavroc\n *\n * DO NOT EDIT DIRECTLY\n */")]]
            ),
            $namespace->getNode(),
        ];

        return (new Standard(['shortArraySyntax' => true]))->prettyPrintFile($ast);
    }

    public function dumpCommonInterface(string $fqcn): string
    {
        $template = <<<TEMPLATE
<?php

/**
 * Autogenerated by jaumo/phavroc
 *
 * DO NOT EDIT DIRECTLY
 */

namespace __NAMESPACE__;

interface __NAME__ extends __SHARED_INTERFACE__
{}
TEMPLATE;

        $namespace = '';
        $name = ltrim($fqcn, '\\');
        if (false !== $pos = strrpos($name, '\\')) {
            $namespace = substr($name, 0, (int) $pos);
            $name = substr($name, ((int) $pos) + 1);
        }

        return strtr($template, [
            '__NAMESPACE__' => $namespace,
            '__NAME__' => $name,
            '__SHARED_INTERFACE__' => '\\' . self::DEFAULT_INTERFACE_NAME,
        ]);
    }

    public function dumpDefaultInterface(): string
    {
        $template = <<<TEMPLATE
<?php

/**
 * Autogenerated by jaumo/phavroc
 *
 * DO NOT EDIT DIRECTLY
 */

namespace __NAMESPACE__;

interface __NAME__
{
    public static function getSchema(): \Avro\Model\Schema\Schema;
    public function normalize();
    public static function denormalize(\$record);
}
TEMPLATE;

        $namespace = '';
        $name = self::DEFAULT_INTERFACE_NAME;
        if (false !== $pos = strrpos($name, '\\')) {
            $namespace = substr($name, 0, (int) $pos);
            $name = substr($name, ((int) $pos) + 1);
        }

        return strtr($template, [
            '__NAMESPACE__' => $namespace,
            '__NAME__' => $name,
        ]);
    }
}
