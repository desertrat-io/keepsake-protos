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

namespace Avro\SchemaRegistry\Model;

use Avro\AvroException;
use Avro\SchemaRegistry\ClientError;

final class Error extends ClientError
{
    private const FIELD_ERROR_CODE = 'error_code';
    private const FIELD_MESSAGE = 'message';

    public const SUBJECT_NOT_FOUND = 40401;
    public const VERSION_NOT_FOUND = 40402;
    public const SCHEMA_NOT_FOUND = 40403;

    public const INVALID_AVRO_SCHEMA = 42201;
    public const INVALID_VERSION = 42202;
    public const INVALID_COMPATIBILITY_LEVEL = 42203;

    public const SERVER_ERROR = 50001;
    public const SERVER_TIMEOUT = 50002;
    public const SERVER_MASTER_ERROR = 50003;

    private const MESSAGES = [
        self::SUBJECT_NOT_FOUND => 'Subject not found',
        self::VERSION_NOT_FOUND => 'Version not found',
        self::SCHEMA_NOT_FOUND => 'Schema not found',
        self::INVALID_AVRO_SCHEMA => 'Invalid Avro schema',
        self::INVALID_VERSION => 'Invalid version',
        self::INVALID_COMPATIBILITY_LEVEL => 'Invalid compatibility level',
        self::SERVER_ERROR => 'Error in the backend datastore',
        self::SERVER_TIMEOUT => 'Operation timed out',
        self::SERVER_MASTER_ERROR => 'Error while forwarding the request to the master',
    ];

    private function __construct(string $reason, int $code)
    {
        $message = self::MESSAGES[$code] ?? 'unknown error';

        if ($reason !== '') {
            $message .= ': ' . $reason;
        }

        parent::__construct($message, $code);
    }

    public static function isError(array $data): bool
    {
        return isset($data[self::FIELD_ERROR_CODE]);
    }

    /**
     * @param array $data
     * @return Error
     *
     * @throws AvroException
     */
    public static function fromResponse(array $data): self
    {
        if (!self::isError($data)) {
            throw new AvroException('Given response is not an error-response');
        }

        $errorCode = $data[self::FIELD_ERROR_CODE];
        $errorInfo = $data[self::FIELD_MESSAGE] ?? '';

        return new self($errorInfo, $errorCode);
    }
}
