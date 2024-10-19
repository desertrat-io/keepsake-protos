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

namespace Phavroc\Avro\Transpiling\Transpiler;

use Avro\Model\Schema\Schema;
use Phavroc\Avro\Transpiling\Package;
use Phavroc\Avro\Transpiling\Transpiler;
use Phavroc\Avro\Transpiling\TranspilerAware;

final class ChainTranspiler implements Transpiler
{
    /** @var Transpiler[] */
    private array $transpilers = [];

    public function addTranspiler(Transpiler $transpiler): void
    {
        if ($transpiler instanceof TranspilerAware) {
            $transpiler->setTranspiler($this);
        }

        $this->transpilers[] = $transpiler;
    }

    public function transpile(Schema $schema, Package $package): Package
    {
        foreach ($this->transpilers as $transpiler) {
            $package = $transpiler->transpile($schema, $package);
        }

        return $package;
    }
}
