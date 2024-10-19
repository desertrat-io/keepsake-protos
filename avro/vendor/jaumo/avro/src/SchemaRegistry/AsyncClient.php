<?php

/**
 * Copyright 2024 Joyride GmbH.
 *
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

namespace Avro\SchemaRegistry;

interface AsyncClient
{
    public const PATH_POST_SCHEMA_REGISTERED = '/subjects/%s';
    public const PATH_POST_REGISTER_SCHEMA = '/subjects/%s/versions';
    public const PATH_GET_SCHEMA = '/schemas/ids/%d';

    public function getRegisteredSchemaId(string $subject, string $schema): ?int;

    public function registerSchema(string $subject, string $schema): int;

    public function getSchema(int $id): string;
}
