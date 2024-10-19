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
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Return_;

final class Standard implements GetterNodesProvider
{
    public function supports(Property $property): bool
    {
        return true;
    }

    public function getNodes(Property $property): array
    {
        $typehint = $property->combinable() ? 'array' : $property->type();

        return [
            (new Method(sprintf('get%s', ucfirst($property->phpName()))))
                ->makePublic()
                ->setReturnType($property->nullable() ? new NullableType($typehint) : $typehint)
                ->addStmt(new Return_(new PropertyFetch(new Variable('this'), $property->phpName()))),
        ];
    }
}
