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

namespace AvroTest\SchemaRegistry;

use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Avro\SchemaRegistry\AmpClient;
use Avro\SchemaRegistry\ClientError;
use Avro\SchemaRegistry\Model\Error;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AmpClientTest extends TestCase
{
    private const BASE_URL = 'http://example.org';

    /**
     * @var AmpClient
     */
    private $client;

    /**
     * @var DelegateHttpClient|MockObject
     */
    private $httpClientMock;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(DelegateHttpClient::class);

        $this->client = new AmpClient(self::BASE_URL, new HttpClient($this->httpClientMock, []));
    }

    public function testGetRegisteredSchemaIdWithKnownSchema(): void
    {
        $request = new Request(self::BASE_URL . '/subjects/foo', 'POST');
        $request->addHeader('Accept', 'application/vnd.schemaregistry.v1+json');
        $request->setBody('{"schema":"\"serialized_schema\""}');

        $this->mockResponse($request, '{"id": 42}');

        $id = $this->client->getRegisteredSchemaId('foo', '"serialized_schema"');
        $this->assertSame(42, $id);
    }

    public function testGetRegisteredSchemaIdWithUnknownSchema(): void
    {
        $request = new Request(self::BASE_URL . '/subjects/foo', 'POST');
        $request->addHeader('Accept', 'application/vnd.schemaregistry.v1+json');
        $request->setBody('{"schema":"\"serialized_schema\""}');

        $this->mockResponse($request, '{"error_code": 40403}');

        $id = $this->client->getRegisteredSchemaId('foo', '"serialized_schema"');
        $this->assertSame(null, $id);
    }

    public function testRegisterSchema()
    {
        $request = new Request(self::BASE_URL . '/subjects/foo/versions', 'POST');
        $request->addHeader('Accept', 'application/vnd.schemaregistry.v1+json');
        $request->setBody('{"schema":"\"serialized_schema\""}');

        $this->mockResponse($request, '{"id": 42}');

        $id = $this->client->registerSchema('foo', '"serialized_schema"');
        $this->assertSame(42, $id);
    }

    /**
     * @dataProvider exampleErrors
     *
     * @param int $code
     * @param string $reason
     * @param string $exceptionMessage
     */
    public function testApiErrors(int $code, string $reason, string $exceptionMessage): void
    {
        $request = new Request(self::BASE_URL . '/subjects/foo/versions', 'POST');
        $request->addHeader('Accept', 'application/vnd.schemaregistry.v1+json');
        $request->setBody('{"schema":"\"serialized_schema\""}');

        $this->mockResponse($request, \sprintf('{"error_code": %d, "message": "%s"}', $code, $reason));

        $this->expectException(Error::class);
        $this->expectExceptionCode($code);
        $this->expectExceptionMessage($exceptionMessage);

        $this->client->registerSchema('foo', '"serialized_schema"');
    }

    public function testInvalidJsonError(): void
    {
        $request = new Request(self::BASE_URL . '/subjects/foo/versions', 'POST');
        $request->addHeader('Accept', 'application/vnd.schemaregistry.v1+json');
        $request->setBody('{"schema":"\"serialized_schema\""}');

        $this->mockResponse($request, '{[');

        $this->expectException(ClientError::class);
        $this->expectExceptionMessage('Failed to parse JSON');

        $this->client->registerSchema('foo', '"serialized_schema"');
    }

    /**
     * @return array
     */
    public function exampleErrors(): array
    {
        return [
            [50001, 'foobar', 'Error in the backend datastore: foobar'],
            [50001, '', 'Error in the backend datastore'],
            [12345, '', 'unknown error'],
            [40403, 'test', 'Schema not found: test'],
        ];
    }

    private function mockResponse(Request $request, string $responseBodyStr): void
    {
        $this
            ->httpClientMock
            ->method('request')
            ->with($this->equalTo($request))
            ->willReturnCallback(function (Request $request) use ($responseBodyStr) {
                return new Response('1.1', 200, 'Ok', [], $responseBodyStr, $request);
            });
    }
}
