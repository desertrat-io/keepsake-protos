<?php

namespace Compiled;

use AvroParser\AvroField;
use AvroParser\AvroRecord;
use JsonSerializable;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

abstract class BaseRecord implements JsonSerializable
{

    /**
     * For more complex defaults, such as records within records, we can set
     */
    protected $defaults = [];

    public function __construct(array $data = [])
    {
        if ($this->defaults) {
            $this->decode($this->defaults);
        }

        if ($data) {
            $this->decode($data);
        }
    }

    public abstract function schema(): string;

    public function name(): string
    {
        $reflect = new ReflectionClass($this);
        return $reflect->getShortName();
    }

    public function setData(array $data)
    {
        $this->decode($data);
    }

    public function data(): array
    {
        return $this->encode($this);
    }

    protected function encode($mixed)
    {
        return json_decode(json_encode($mixed), true);
    }

    /**
     * @param array $data - Array holding arbitrary data to be decoded into this object.
     * @throws ReflectionException
     * @throws \AvroParser\NotImplementedException
     */
    public function decode(array $data)
    {

        // Instantiate an AvroRecord for this so we can decode using the schema
        $record = AvroRecord::create(json_decode($this->schema()), 'one.two');

        /** @var AvroField[] $fieldMap */
        $fieldMap = array_reduce($record->fields, function (array $carry, AvroField $field) {
            $carry[$field->name] = $field;
            return $carry;
        }, []);

        $refl = new ReflectionClass($this);

        foreach ($data as $propertyToSet => $value) {

            try {
                $prop = $refl->getProperty($propertyToSet);
            } catch (ReflectionException $e) {
                continue;
            }

            if (!$prop instanceof ReflectionProperty) {
                continue;
            }

            $propName = $prop->getName();
            $field = $fieldMap[$propName];

            // Decode using the type
            $value = $field->type->decode($value, __NAMESPACE__);

            $prop->setAccessible(true);
            $prop->setValue($this, $value);
        }
    }

}