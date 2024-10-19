![Banner](./banner.png)

# Phavroc

## Introduction

This library allows to generate modern [PHP](http://php.net/) classes from [Avro](http://avro.apache.org/) Schemas.

## Installation

This library is registered on [packagist.org](https://packagist.org/packages/jaumo/phavroc), allowing you to install it using [Composer](https://getcomposer.org/).

Run the following command to do so:

```
$ composer require jaumo/phavroc
```

# Usage

## Basic

Once installed, you may run the generator against a specific Avro Schema using:

```
$ ./vendor/bin/phavroc my_schema.avsc
```

or against a whole directory using:

```
$ ./vendor/bin/phavroc schemas/
```

## Common Interface

It is possible to specify a common interface for all the generated class:

```
$ ./vendor/bin/phavroc --common-interface Com\\Acme\\MessageInterface schemas/
```

This is especially useful to easily typehint all the messages at once.

## Deprecations support

It is possible to provide a JSON map-file with info about deprecated schemas/fields:

```
$ ./vendor/bin/phavroc --deprecation-map deprecations.json schemas/
```
The structure of this mapping is as follows:
```
{
    "fully.qualified.schema.Name": {
        "deprecated": "Optional field. Contains the schema-level deprecation doc",
        "deprecated-fields": [
            {
                "name": "Name of a deprecated field",
                "doc": "Deprecation doc"
            },
            ...
        ],
        "deprecated-symbols": [
            "ENUM_SYMBOL"
        ]
    }
}
```
The corresponding deprecation docs will be included in doc-blocks of the generated classes.

# Contributing

This library is released under the Apache-2.0 license (see [LICENSE](./LICENSE) for more information).

Any contributions are very welcome, make sure to do the appropriate changes to the [Behat](http://behat.org/) scenarios as well.

You may execute the test suite by running:

```
$ ./vendor/bin/behat
```
