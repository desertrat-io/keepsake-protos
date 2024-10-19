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

namespace Phavroc\PhpDumper\PhpParser\Getter;

use Phavroc\Avro\Transpiling\Property;
use Phavroc\PhpDumper\PhpParser\GetterNodesProvider;
use PhpParser\Builder\Method;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Cast\Double as DoubleCast;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;

final class TimestampMillis implements GetterNodesProvider
{
    public function supports(Property $property): bool
    {
        return 'timestamp-millis' === $property->logicalType();
    }

    public function getNodes(Property $property): array
    {
        $returnStmt = new StaticCall(
            new FullyQualified('DateTimeImmutable'),
            new Identifier('createFromFormat'),
            [
                new Arg(new String_('U.u')),
                new Arg(new FuncCall(
                    new Name('number_format'),
                    [
                        new Arg(
                            new DoubleCast(new Div(
                                new PropertyFetch(new Variable('this'), new Identifier($property->phpName())),
                                new LNumber(1000)
                            ))
                        ),
                        new Arg(new LNumber(3)),
                        new Arg(new String_('.')),
                        new Arg(new String_('')),
                    ]
                )),
            ]
        );
        if ($property->nullable()) {
            $returnStmt = new Ternary(
                new NotIdentical(new ConstFetch(new Name('null')), new PropertyFetch(new Variable('this'), $property->phpName())),
                $returnStmt,
                new ConstFetch(new Name('null'))
            );
        }

        return [
            (new Method(sprintf('get%s', ucfirst($property->phpName()))))
                ->makePublic()
                ->setReturnType($property->nullable() ? new NullableType('\DateTimeImmutable') : '\DateTimeImmutable')
                ->addStmt(new Return_($returnStmt)),
        ];
    }
}
