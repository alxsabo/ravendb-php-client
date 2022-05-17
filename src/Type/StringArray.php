<?php

namespace RavenDB\Type;

class StringArray extends ExtendedArrayObject implements \JsonSerializable
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function withValue(string $id): StringArray
    {
        $a = new StringArray();
        $a->append($id);
        return $a;
    }

    public static function fromArray(array $data): StringArray
    {
        $sa = new StringArray();

        foreach ($data as $key => $value) {
            $sa->offsetSet($key, $value);
        }

        return $sa;
    }

    public function offsetSet($key, $value)
    {
        if (!is_string($value)) {
            throw new \TypeError(
                sprintf("Only values of type string are supported")
            );
        }

        parent::offsetSet($key, $value);
    }

    public function isEmpty(): bool
    {
        return $this->count() == 0;
    }

    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }

    public function hasValue(string $fieldName): bool
    {
        return in_array($fieldName, $this->getArrayCopy());
    }

    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
