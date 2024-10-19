<?php

namespace Compiled\Records;

use Compiled\BaseRecord;

class Thing extends BaseRecord
{

    /** @var int */
    private $id;

    /** @return int */
    public function getId(): int
    {
        return $this->id;
    }

    /** @param int $id */
    public function setId(int $id): Thing
    {
        $this->id = $id;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            "id" => $this->encode($this->id)
        ];
    }

    public function schema(): string
    {
        return <<<SCHEMA
{
    "type": "record",
    "name": "Thing",
    "namespace": "records",
    "fields": [
        {
            "name": "id",
            "type": "int"
        }
    ]
}
SCHEMA;
    }

}