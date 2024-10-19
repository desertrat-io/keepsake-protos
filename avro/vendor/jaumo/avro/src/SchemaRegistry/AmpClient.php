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

/**
 * Copyright 2020 Joyride GmbH.
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

namespace Avro\SchemaRegistry;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Request;
use Avro\SchemaRegistry\Model\Error;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\StringsException;

class AmpClient implements AsyncClient
{
    private const ACCEPT_MIME_TYPE = 'application/vnd.schemaregistry.v1+json';

    private HttpClient $client;
    private string $baseUri;

    public function __construct(string $baseUri, HttpClient $client)
    {
        $this->baseUri = \rtrim($baseUri, '/');
        $this->client = $client;
    }

    public function getRegisteredSchemaId(string $subject, string $schema): ?int
    {
        try {
            $json = $this->jsonRequest(
                self::PATH_POST_SCHEMA_REGISTERED,
                [$subject],
                'POST',
                ['schema' => $schema]
            );

            return $json['id'];
        } catch (ClientError $e) {
            if (\in_array($e->getCode(), [Error::SUBJECT_NOT_FOUND, Error::SCHEMA_NOT_FOUND], true)) {
                return null;
            }

            throw $e;
        }
    }

    public function registerSchema(string $subject, string $schema): int
    {
        $json = $this->jsonRequest(
            self::PATH_POST_REGISTER_SCHEMA,
            [$subject],
            'POST',
            ['schema' => $schema]
        );

        return $json['id'];
    }

    public function getSchema(int $id): string
    {
        $json = $this->jsonRequest(
            self::PATH_GET_SCHEMA,
            [$id]
        );

        return $json['schema'];
    }

    /**
     * @param non-empty-string $method
     */
    private function jsonRequest(
        string $path,
        array $params,
        string $method = 'GET',
        array $body = []
    ): array {
        $response = $this->client->request(
            $this->buildRequest($path, $params, $method, \Safe\json_encode($body))
        );

        $raw = $response->getBody()->buffer();
        try {
            $json = \Safe\json_decode($raw, true);
            if (Error::isError($json)) {
                throw Error::fromResponse($json);
            }

            return $json;
        } catch (JsonException $e) {
            throw ClientError::jsonParseFailed($raw, $e);
        }
    }

    /**
     * @param non-empty-string $method
     */
    private function buildRequest(string $path, array $params, string $method, string $body): \Amp\Http\Client\Request
    {
        try {
            $uri = $this->baseUri . \Safe\sprintf($path, ...$params);

            $request = new Request($uri, $method);
            $request->addHeader('Accept', self::ACCEPT_MIME_TYPE);
            $request->setBody($body);

            return $request;
        } catch (StringsException $e) {
            throw ClientError::buildRequestFailed($e);
        }
    }
}
