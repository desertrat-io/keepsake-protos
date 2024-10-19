<?php

namespace Compiled\Example\Avro;

use Compiled\BaseRecord;

class User extends BaseRecord
{

    /** @var string */
    private $name;

    /** @var string|null */
    private $favorite_color;

    /** @var int[] */
    private $favorite_numbers;

    /** @return string */
    public function getName(): string
    {
        return $this->name;
    }

    /** @param string $name */
    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /** @return string|null */
    public function getFavorite_color()
    {
        return $this->favorite_color;
    }

    /** @param string|null $favorite_color */
    public function setFavorite_color($favorite_color): User
    {
        $this->favorite_color = $favorite_color;
        return $this;
    }

    /** @return int[] */
    public function getFavorite_numbers(): array
    {
        return $this->favorite_numbers;
    }

    /** @param int[] $favorite_numbers */
    public function setFavorite_numbers(array $favorite_numbers): User
    {
        $this->favorite_numbers = $favorite_numbers;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            "name" => $this->encode($this->name),
            "favorite_color" => $this->encode($this->favorite_color),
            "favorite_numbers" => $this->encode($this->favorite_numbers)
        ];
    }

    public function schema(): string
    {
        return <<<SCHEMA
{
    "type": "record",
    "namespace": "example.avro",
    "name": "User",
    "fields": [
        {
            "type": "string",
            "name": "name"
        },
        {
            "type": [
                "string",
                "null"
            ],
            "name": "favorite_color"
        },
        {
            "type": {
                "items": "int",
                "type": "array"
            },
            "name": "favorite_numbers"
        }
    ]
}
SCHEMA;
    }

}