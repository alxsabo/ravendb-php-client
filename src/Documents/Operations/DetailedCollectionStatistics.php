<?php

namespace RavenDB\Documents\Operations;

class DetailedCollectionStatistics
{
    private ?int $countOfDocuments = null;
    private ?int $countOfConflicts = null;
    private ?CollectionDetailsArray $collections = null;

    public function getCountOfDocuments(): ?int
    {
        return $this->countOfDocuments;
    }

    public function setCountOfDocuments(?int $countOfDocuments): void
    {
        $this->countOfDocuments = $countOfDocuments;
    }

    public function getCountOfConflicts(): ?int
    {
        return $this->countOfConflicts;
    }

    public function setCountOfConflicts(?int $countOfConflicts): void
    {
        $this->countOfConflicts = $countOfConflicts;
    }

    public function getCollections(): ?CollectionDetailsArray
    {
        return $this->collections;
    }

    public function setCollections(null|CollectionDetailsArray|array $collections): void
    {
        if (is_array($collections)) {
            $collections = CollectionDetailsArray::fromArray($collections);
        }
        $this->collections = $collections;
    }
}
