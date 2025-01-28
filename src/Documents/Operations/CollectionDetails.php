<?php

namespace RavenDB\Documents\Operations;

use RavenDB\Utils\Size;

class CollectionDetails
{
    private ?string $name = null;
    private ?int $countOfDocuments = null;
    private ?Size $size = null;
    private ?Size $documentsSize = null;
    private ?Size $tombstonesSize = null;
    private ?Size $revisionsSize = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCountOfDocuments(): ?int
    {
        return $this->countOfDocuments;
    }

    public function setCountOfDocuments(?int $countOfDocuments): void
    {
        $this->countOfDocuments = $countOfDocuments;
    }

    public function getSize(): ?Size
    {
        return $this->size;
    }

    public function setSize(?Size $size): void
    {
        $this->size = $size;
    }

    public function getDocumentsSize(): ?Size
    {
        return $this->documentsSize;
    }

    public function setDocumentsSize(?Size $documentsSize): void
    {
        $this->documentsSize = $documentsSize;
    }

    public function getTombstonesSize(): ?Size
    {
        return $this->tombstonesSize;
    }

    public function setTombstonesSize(?Size $tombstonesSize): void
    {
        $this->tombstonesSize = $tombstonesSize;
    }

    public function getRevisionsSize(): ?Size
    {
        return $this->revisionsSize;
    }

    public function setRevisionsSize(?Size $revisionsSize): void
    {
        $this->revisionsSize = $revisionsSize;
    }
}
