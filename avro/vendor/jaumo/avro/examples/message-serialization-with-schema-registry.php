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

require __DIR__ . '/../vendor/autoload.php';

use Amp\Http\Client\HttpClientBuilder;
use Avro\SchemaRegistry\AmpClient;
use Avro\SchemaRegistry\CachedClient;
use Avro\SchemaRegistry\DefaultSerializer;
use Avro\SchemaRegistry\Options;

$json = <<<JSON
{
  "type": "record",
  "name": "com.avro.Message",
  "fields": [
    {
      "name": "foo",
      "type": {
        "type": "record",
        "name": "Plop",
        "fields": [
          {
            "name": "attr",
            "type": "string"
          }
        ]
      }
    },
    {
      "name": "bar",
      "type": "Plop"
    }
  ]
}
JSON;
$schema = \Avro\Serde::parseSchema($json);

$serializer = new DefaultSerializer(
    new CachedClient(
        new \Doctrine\Common\Cache\ArrayCache(),
        new AmpClient('http://schema-registry:8081/', HttpClientBuilder::buildDefault())
    ),
    (new Options())->enableAutoSchemaRegistration()
);
$data = \Avro\Serde::encodeMessageWithSchemaRegistry(
    $schema,
    [
        'foo' => ['attr' => 'FOO'],
        'bar' => ['attr' => 'FOO'],
    ],
    'example1',
    $serializer
);
(\var_dump($data));

$data = \Avro\Serde::decodeMessageWithSchemaRegistry(
    $data,
    $serializer
);

(\var_dump($data->getValue()));
