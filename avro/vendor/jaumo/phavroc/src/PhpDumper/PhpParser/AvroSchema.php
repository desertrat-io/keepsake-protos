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
use Phavroc\Avro\Transpiling\Enum as EnumClass;
use Phavroc\PhpDumper\DeprecationMap;
use PhpParser\Builder\Method;
use PhpParser\Builder\Property;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\VarLikeIdentifier;

final class AvroSchema implements NodesProvider
{
    public function supports(Class_ $class): bool
    {
        return true;
    }

    public function getNodes(Class_ $class, ?DeprecationMap $deprecationMap): array
    {
        if ($class instanceof EnumClass) {
            return [
                (new Method('getSchema'))
                    ->makePublic()
                    ->makeStatic()
                    ->setReturnType('\Avro\Model\Schema\Schema')
                    ->addStmt(new Return_(new StaticCall(new Name('\Avro\Serde'), new Identifier('parseSchema'), [
                        new Arg(new String_($class->schema())),
                    ])))
            ];
        }
        return [
            (new Property('schema'))->makePrivate()->makeStatic()->setDocComment('/** @var null|\Avro\Model\Schema\Schema */'),
            (new Method('getSchema'))
                ->makePublic()
                ->makeStatic()
                ->setReturnType('\Avro\Model\Schema\Schema')
                ->addStmt(new If_(
                    new Identical(
                        new ConstFetch(new Name('null')),
                        new StaticPropertyFetch(new Name('self'), new VarLikeIdentifier('schema'))
                    ),
                    [
                        'stmts' => [
                            new Expression(new Assign(
                                new StaticPropertyFetch(new Name('self'), new VarLikeIdentifier('schema')),
                                new StaticCall(new Name('\Avro\Serde'), new Identifier('parseSchema'), [
                                    new Arg(new String_($class->schema())),
                                ])
                            )),
                        ],
                    ]
                ))
                ->addStmt(new Return_(new StaticPropertyFetch(new Name('self'), new VarLikeIdentifier('schema')))),
        ];
    }
}
