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
use Phavroc\Avro\Transpiling\Class_;
use Phavroc\Avro\Transpiling\DTO;
use Phavroc\PhpDumper\DeprecationMap;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;

final class DTONormalization implements NodesProvider
{
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
        $normalizeItems = [];
        $denormalizeNodes = [new Assign(new Variable('self'), new New_(new Name('self')))];
        $logicalTypeNullChecks = [];
        foreach ($class->properties() as $property) {
            $propertyFetch = $normalizeCall = new PropertyFetch(new Variable('this'), $property->phpName());

            if (!$property->scalar()) {
                $normalizeCall = $value = new MethodCall($propertyFetch, new Identifier('normalize'));
                if ($property->combinable()) {
                    $normalizeCall = $value = new FuncCall(new Name('array_map'), [
                        new Arg(new Closure([
                            'params' => [(new Param('item'))->getNode()],
                            'stmts' => [
                                new Return_(
                                    new Ternary(
                                        new NotIdentical(new ConstFetch(new Name('null')), new Variable('item')),
                                        new MethodCall(new Variable('item'), new Identifier('normalize')),
                                        new ConstFetch(new Name('null'))
                                    )
                                ),
                            ],
                        ])),
                        new Arg($propertyFetch),
                    ]);
                }

                if ($property->nullable()) {
                    $value = new Ternary(
                        new NotIdentical(new ConstFetch(new Name('null')), $propertyFetch),
                        $normalizeCall,
                        new ConstFetch(new Name('null'))
                    );
                }
            } elseif ($property->logicalType()) {
                // for logical types getters return different types than stored, so we can't use a getter
                $value = $propertyFetch;

                // If the property does not have a default value, we explicitly check whether i
                if (!$property->hasDefaultValue() && !$property->nullable()) {
                    $logicalTypeNullCheck = new If_(new BooleanNot(new Isset_([$propertyFetch])));
                    $logicalTypeNullCheck->stmts = [
                        new Throw_(
                            new New_(
                                new Name('\\Exception'),
                                [new Arg(new String_($property->phpName() . ' is not initialized yet'))]
                            )
                        ),
                    ];
                    $logicalTypeNullChecks[] = $logicalTypeNullCheck;
                }
            } else {
                $value = new MethodCall(new Variable('this'), sprintf('get%s', ucfirst($property->phpName())));
            }
            $normalizeItems[] = new ArrayItem($value, new String_($property->avroName()));

            $denormalizeCall = new ArrayDimFetch(new Variable('record'), new String_($property->avroName()));
            if ($property->scalar()) {
                if ($property->nullable()) {
                    $denormalizeCall = new Coalesce($denormalizeCall, new ConstFetch(new Name('null')));
                }
            } else {
                if ($property->combinable()) {
                    $arrayMapCall = new FuncCall(new Name('array_map'), [
                        new Arg(new Closure([
                            'params' => [(new Param('item'))->getNode()],
                            'stmts' => [new Return_(
                                new Ternary(
                                    new NotIdentical(new ConstFetch(new Name('null')), new Variable('item')),
                                    new StaticCall(new Name($property->type()), 'denormalize', [new Arg(new Variable('item'))]),
                                    new ConstFetch(new Name('null'))
                                )
                            )],
                        ])),
                        new Arg(new ArrayDimFetch(new Variable('record'), new String_($property->avroName()))),
                    ]);

                    $denormalizeCall = $property->nullable()
                        ?
                        new Ternary(
                            new Isset_([new ArrayDimFetch(new Variable('record'), new String_($property->avroName()))]),
                            $arrayMapCall,
                            new ConstFetch(new Name('null'))
                        )
                        : $arrayMapCall;
                } else {
                    if ($property->nullable()) {
                        $denormalizeCall = new Ternary(
                            new Isset_([
                                new ArrayDimFetch(new Variable('record'), new String_($property->avroName())),
                            ]),
                            new StaticCall(new Name($property->type()), 'denormalize', [new Arg($denormalizeCall)]),
                            new ConstFetch(new Name('null'))
                        );
                    } else {
                        $denormalizeCall = new StaticCall(new Name($property->type()), 'denormalize', [new Arg($denormalizeCall)]);
                    }
                }
            }
            $denormalizeNodes[] = new Expression(new MethodCall(
                new Variable('self'),
                new Identifier(sprintf('set%s', ucfirst($property->phpName()))),
                [new Arg($denormalizeCall)]
            ));

            $typehint = $property->combinable() ? 'array' : $property->type();
            if ('void' === $typehint) {
                $nodes[] = (new Method(sprintf('set%s', ucfirst($property->phpName()))))
                    ->makePrivate()
                    ->setReturnType('void');
                continue;
            }
            $nodes[] = (new Method(sprintf('set%s', ucfirst($property->phpName()))))
                ->makePrivate()
                ->addParam((new Param($property->phpName()))->setType($property->nullable() ? new NullableType($typehint) : $typehint))
                ->setReturnType('void')
                ->addStmt(new Assign(
                    new PropertyFetch(new Variable('this'), new Identifier($property->phpName())),
                    new Variable($property->phpName())
                ));
        }

        $normalize = (new Method('normalize'))
            ->makePublic()
            ->setReturnType('array');
        if (!empty($logicalTypeNullChecks)) {
            $normalize->addStmts($logicalTypeNullChecks);
        }
        $normalize->addStmt(new Return_(new Array_($normalizeItems)));
        $nodes[] = $normalize;

        $checkType = new If_(new BooleanNot(new FuncCall(
            new Name('is_array'),
            [new Arg(new Variable('record'))]
        )));
        $checkType->stmts = [
            new Throw_(new New_(new Name('\\' . InvalidArgumentException::class), [new Arg(new String_('Records can only be deserialized from array'))])),
        ];

        $nodes[] = (new Method('denormalize'))
            ->makePublic()
            ->makeStatic()
            ->addParam(new Param('record'))
            ->setReturnType('self')
            ->addStmt($checkType)
            ->addStmts($denormalizeNodes)
            ->addStmt(new Return_(new Variable('self')));

        return $nodes;
    }
}
