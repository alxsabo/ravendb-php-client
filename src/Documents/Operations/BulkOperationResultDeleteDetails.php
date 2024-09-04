<?php

namespace RavenDB\Documents\Operations;

class BulkOperationResultDeleteDetails implements BulkOperationDetailsInterface
{
    private ?string $id = null;
    public ?int $etag = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getEtag(): ?int
    {
        return $this->etag;
    }

    public function setEtag(?int $etag): void
    {
        $this->etag = $etag;
    }

    public static function fromArray(array $data): BulkOperationResultDeleteDetails
    {
        $object = new BulkOperationResultDeleteDetails();

        if (array_key_exists('Id', $data)) {
            $object->setId($data['Id']);
        }

        if (array_key_exists('ETag', $data)) {
            $object->setEtag($data['ETag']);
        }

        return $object;
    }
}
