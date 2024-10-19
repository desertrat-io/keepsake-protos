<?php

namespace Compiled\Sample\Common;

use Compiled\BaseRecord;

class SharedMeta extends BaseRecord
{

    /** @var string */
    private $uuid;

    /** @return string */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /** @param string $uuid */
    public function setUuid(string $uuid): SharedMeta
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            "uuid" => $this->encode($this->uuid)
        ];
    }

    public function schema(): string
    {
        return <<<SCHEMA
{
    "type": "record",
    "name": "SharedMeta",
    "namespace": "sample.common",
    "fields": [
        {
            "name": "uuid",
            "type": "string"
        }
    ]
}
SCHEMA;
    }

}