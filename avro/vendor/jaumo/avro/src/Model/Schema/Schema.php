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

namespace Avro\Model\Schema;

interface Schema
{
    public const ATTR_TYPE = 'type';

    public const ATTR_LOGICAL_TYPE = 'logicalType';

    public const LOGICAL_TYPE_DECIMAL = 'decimal';

    public const ATTR_LOGICAL_TYPE_DECIMAL_PRECISION = 'precision';

    public const ATTR_LOGICAL_TYPE_DECIMAL_SCALE = 'scale';

    public const LOGICAL_TYPE_DATE = 'date';

    public const LOGICAL_TYPE_TIME_MILLIS = 'time-millis';

    public const LOGICAL_TYPE_TIME_MICROS = 'time-micros';

    public const LOGICAL_TYPE_TIMESTAMP_MILLIS = 'timestamp-millis';

    public const LOGICAL_TYPE_TIMESTAMP_MICROS = 'timestamp-micros';

    public const LOGICAL_TYPE_DURATION = 'duration';
}
