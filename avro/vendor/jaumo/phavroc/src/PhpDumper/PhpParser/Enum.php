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

use InvalidArgumentException;
use Phavroc\Avro\Transpiling\Class_ as TranspiledClass;
use Phavroc\Avro\Transpiling\Enum as EnumClass;
use Phavroc\PhpDumper\DeprecationMap;
use PhpParser\Builder\ClassConst;
use PhpParser\Builder\EnumCase;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;

final class Enum implements NodesProvider
{
    private BuilderFactory $builderFactory;

    public function __construct(BuilderFactory $builderFactory = null)
    {
        $this->builderFactory = $builderFactory ?? new BuilderFactory();
    }

    public function supports(TranspiledClass $class): bool
    {
        return $class instanceof EnumClass;
    }

    public function getNodes(TranspiledClass $class, ?DeprecationMap $deprecationMap): array
    {
        if (!$class instanceof EnumClass) {
            return [];
        }

        $nodes = [];

        $nameMapItems = [];
        foreach ($class->options() as $key => $option) {
            $prepend = str_starts_with($option, '_');
            $append = str_ends_with($option, '_');

            $name = str_replace('_', '', ucwords(strtolower(trim($option, '_')), '_'));

            if ($prepend) {
                $name = '_' . $name;
            }

            if ($append) {
                $name .= '_';
            }

            $enumCase = (new EnumCase($name))->setValue($key);
            if ($deprecationMap?->isSymbolDeprecated($class->fqcn(), $option)) {
                $enumCase->setDocComment('/** @deprecated */');
            }
            $nodes[] = $enumCase;
            $nameMapItems[] = new ArrayItem(new String_($option), new String_($name));
        }

        $nodes[] = (new ClassConst('NAME_MAP', new Array_($nameMapItems)))->makePrivate();

        $nodes[] = (new Method('normalize'))
            ->makePublic()
            ->setReturnType('string')
            ->addStmt(new Return_(new ArrayDimFetch(
                new ConstFetch(new Name('self::NAME_MAP')),
                new PropertyFetch(new Variable('this'), 'name')
            )));

        $checkType = new If_(new BooleanNot(new FuncCall(new Name('is_string'), [new Arg(new Variable('name'))])));
        $checkType->stmts = [
            new Throw_(new New_(new Name('\\' . InvalidArgumentException::class), [
                new Arg(new String_('Enums can only be deserialized from strings'))
            ]))];

        $checkNotPascalCaseName = new If_(new Identical(new Variable('pascalCaseName'), new ConstFetch(new Name('false'))));

        $checkIsSet = new If_(new FuncCall(new Name('isset'), [new Arg(new ArrayDimFetch(
            new ConstFetch(new Name('self::NAME_MAP')),
            new Variable('name')
        ))]));
        $checkIsSet->stmts = [new Expression(new Assign(new Variable('pascalCaseName'), new Variable('name')))];
        $checkIsSet->else = new Else_([new Throw_(new New_(new Name('\\' . InvalidArgumentException::class), [
            new Arg(new String_('Unknown name for deserialization'))
        ]))]);

        $checkNotPascalCaseName->stmts = [$checkIsSet];

        $nodes[] = (new Method('denormalize'))
            ->makePublic()
            ->makeStatic()
            ->setReturnType('self')
            ->addParam(new Param('name'))
            ->addStmt($checkType)
            ->addStmt(new Assign(
                new Variable('pascalCaseName'),
                new FuncCall(
                    new Name('array_search'),
                    [
                        new Arg(new Variable('name')),
                        new Arg(new ConstFetch(new Name('self::NAME_MAP')))
                    ]
                )
            ))
            ->addStmt($checkNotPascalCaseName)
            ->addStmt(new Return_(new FuncCall(
                new Name('constant'),
                [
                    new Arg(new Concat(
                        new Concat(new FuncCall(new Name('get_class'), []), new String_('::')),
                        new Variable('pascalCaseName')
                    ))
                ]
            )));

        return $nodes;
    }
}
