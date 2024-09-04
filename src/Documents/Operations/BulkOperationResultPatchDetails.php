<?php

namespace RavenDB\Documents\Operations;

class BulkOperationResultPatchDetails implements BulkOperationDetailsInterface
{
    public ?string $id = null;
    public ?string $changeVector = null;
    public ?PatchStatus $status = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getChangeVector(): ?string
    {
        return $this->changeVector;
    }

    public function setChangeVector(?string $changeVector): void
    {
        $this->changeVector = $changeVector;
    }

    public function getStatus(): ?PatchStatus
    {
        return $this->status;
    }

    public function setStatus(?PatchStatus $status): void
    {
        $this->status = $status;
    }

    public static function fromArray(array $data): BulkOperationResultPatchDetails
    {
        $object = new BulkOperationResultPatchDetails();

        if (array_key_exists('Id', $data)) {
            $object->setId($data['Id']);
        }

        if (array_key_exists('ChangeVector', $data)) {
            $object->setChangeVector($data['ChangeVector']);
        }

        if (array_key_exists('Status', $data)) {
            $object->setStatus($data['Status']);
        }

        return $object;
    }
}
