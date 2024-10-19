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

namespace Phavroc\PhpDumper\PhpParser;

use Phavroc\Avro\Transpiling\Class_;
use Phavroc\Avro\Transpiling\DTO;
use Phavroc\PhpDumper\DeprecationMap;
use PhpParser\Builder\Method;
use PhpParser\Builder\Property;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;

final class GetSet implements NodesProvider
{
    /** @var GetterNodesProvider[] */
    private array $getterProviders = [];

    /** @var SetterNodesProvider[] */
    private array $setterProviders = [];

    /**
     * @param  GetterNodesProvider[] $getterProviders
     * @param  SetterNodesProvider[] $setterProviders
     */
    public function __construct(array $getterProviders, array $setterProviders)
    {
        $this->getterProviders = $getterProviders;
        $this->setterProviders = $setterProviders;
    }

    public function supports(Class_ $class): bool
    {
        return $class instanceof DTO;
    }

    public function getNodes(Class_ $class, ?DeprecationMap $deprecationMap): array
    {
        if (!$class instanceof DTO) {
            return [];
        }

        $nodes = [];
        foreach ($class->properties() as $property) {
            $getter = sprintf('get%s', ucfirst($property->phpName()));

            if ('void' === $property->type()) {
                $nodes[] = (new Method($getter))
                    ->makePublic()
                    ->setReturnType($property->type())
                    ->addStmt(new Return_());

                $nodes[] = (new Method(sprintf('with%s', ucfirst($property->phpName()))))
                    ->makePublic()
                    ->setReturnType('self')
                    ->addStmts([
                        new Assign(new Variable('self'), new Clone_(new Variable('this'))),
                        new Return_(new Variable('self')),
                    ]);
                continue;
            }

            $propertyDeprecation = $deprecationMap?->getFieldDeprecation($class->fqcn(), $property->avroName());

            $propTypeDoc = sprintf(
                $property->combinable() ? '@var %s[]%s' : '@var %s%s',
                $property->nullable() ? sprintf('null|%s', $property->type()) : $property->type(),
                $property->doc() ? ' ' . $property->doc() : ''
            );

            $docComment = $propertyDeprecation
                ? "/**\n * $propTypeDoc\n * @deprecated $propertyDeprecation\n */"
                : "/** $propTypeDoc */";

            $propNode = (new Property($property->phpName()))
                ->makePrivate()
                ->setDocComment($docComment);
            if ($property->hasDefaultValue()) {
                $propNode->setDefault($property->defaultValue());
            }
            $nodes[] = $propNode;

            $gettersAndSetters = [];
            foreach ($this->getterProviders as $provider) {
                if ($provider->supports($property)) {
                    $gettersAndSetters = $provider->getNodes($property);
                    break;
                }
            }

            foreach ($this->setterProviders as $provider) {
                if ($provider->supports($property)) {
                    $gettersAndSetters = array_merge($gettersAndSetters, $provider->getNodes($property));
                    break;
                }
            }

            if ($propertyDeprecation) {
                foreach ($gettersAndSetters as $node) {
                    if ($node instanceof Method) {
                        $node->setDocComment("/** @deprecated $propertyDeprecation */");
                    }
                }
            }

            $nodes = array_merge($nodes, $gettersAndSetters);
        }

        return $nodes;
    }
}
