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

namespace Phavroc\PhpDumper\PhpParser\Setter;

use Phavroc\Avro\Transpiling\Property;
use Phavroc\PhpDumper\PhpParser\SetterNodesProvider;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Return_;

final class Date implements SetterNodesProvider
{
    public function supports(Property $property): bool
    {
        return 'date' === $property->logicalType();
    }

    public function getNodes(Property $property): array
    {
        $assignment = new Int_(
            new Div(
                new MethodCall(new Variable($property->phpName()), new Identifier('getTimestamp')),
                new LNumber(86400)
            )
        );

        if ($property->nullable()) {
            $assignment = new Ternary(
                new NotIdentical(
                    (new BuilderFactory())->constFetch('null'),
                    new Variable($property->phpName())
                ),
                $assignment,
                (new BuilderFactory())->constFetch('null')
            );
        }

        return [
            (new Method(sprintf('with%s', ucfirst($property->phpName()))))
            ->makePublic()
            ->addParam((new Param($property->phpName()))->setType($property->nullable() ? new NullableType('\DateTimeInterface') : '\DateTimeInterface'))
            ->setReturnType('self')
            ->addStmts([
                new Assign(new Variable('self'), new Clone_(new Variable('this'))),
                new Assign(
                    new PropertyFetch(new Variable('self'), new Identifier($property->phpName())),
                    $assignment
                ),
                new Return_(new Variable('self')),
            ]),
        ];
    }
}
