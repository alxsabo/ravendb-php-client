<?php

namespace RavenDB\Documents\Operations;

class BulkOperationResultOperationDetails
{
    public ?string $query = null;

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    public static function fromArray(array $data): BulkOperationResultOperationDetails
    {
        $object = new BulkOperationResultOperationDetails();

        if (array_key_exists('Query', $data)) {
            $object->setQuery($data['Query']);
        }

        return $object;
    }
}
