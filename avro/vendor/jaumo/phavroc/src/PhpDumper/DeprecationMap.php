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

final class DeprecationMap
{
    /**
     * @var array<string, array{
     *     deprecated?: string,
     *     deprecated-fields: array<array{name: string, doc: string}>,
     *     deprecated-symbols: array<string>
     * }>
     */
    private array $mapping = [];

    private bool $initialized = false;

    public function __construct(private readonly string $mappingPath)
    {
    }

    private function initIfNeeded(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $jsonMap = file_get_contents($this->mappingPath);
        if ($jsonMap === false) {
            throw new \RuntimeException("Can't read file under $this->mappingPath");
        }
        $map = json_decode($jsonMap, true);

        $toPhpFqcn = static function (string $avroFqcn): string {
            return strtr(ucwords($avroFqcn, '_.'), ['_' => '', '.' => '\\']);
        };

        foreach ($map as $key => $value) {
            $this->mapping[$toPhpFqcn($key)] = [
                'deprecated' => $value['deprecated'] ?? null,
                'deprecated-fields' => $value['deprecated-fields'] ?? [],
                'deprecated-symbols' => $value['deprecated-symbols'] ?? []
            ];
        }
    }

    public function getSchemaDeprecation(string $schemaFqcn): ?string
    {
        $this->initIfNeeded();

        return $this->mapping[$schemaFqcn]['deprecated'] ?? null;
    }

    public function getFieldDeprecation(string $schemaFqcn, string $fieldName): ?string
    {
        $this->initIfNeeded();

        foreach ($this->mapping[$schemaFqcn]['deprecated-fields'] ?? [] as $item) {
            if ($item['name'] === $fieldName) {
                return $item['doc'];
            }
        }
        return null;
    }

    public function isSymbolDeprecated(string $schemaFqcn, string $symbol): bool
    {
        $this->initIfNeeded();

        return \in_array($symbol, $this->mapping[$schemaFqcn]['deprecated-symbols'] ?? [], true);
    }
}
