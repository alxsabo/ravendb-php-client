<?php

namespace RavenDB\ServerWide;

use RavenDB\Type\StringArray;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CompactSettings
{
    #[SerializedName("DatabaseName")]
    private ?string $databaseName = null;
    #[SerializedName("Documents")]
    private bool $documents = false;
    #[SerializedName("Indexes")]
    private ?StringArray $indexes = null;
    #[SerializedName("SkipOptimizeIndexes")]
    private bool $skipOptimizeIndexes = false;

    public function getDatabaseName(): ?string
    {
        return $this->databaseName;
    }

    public function setDatabaseName(?string $databaseName): void
    {
        $this->databaseName = $databaseName;
    }

    public function isDocuments(): bool
    {
        return $this->documents;
    }

    public function setDocuments(bool $documents): void
    {
        $this->documents = $documents;
    }

    public function getIndexes(): ?StringArray
    {
        return $this->indexes;
    }

    public function setIndexes(null|array|StringArray $indexes): void
    {
        if (is_array($indexes)) {
            $indexes = StringArray::fromArray($indexes);
        }
        $this->indexes = $indexes;
    }

    public function isSkipOptimizeIndexes(): bool
    {
        return $this->skipOptimizeIndexes;
    }

    public function setSkipOptimizeIndexes(bool $skipOptimizeIndexes): void
    {
        $this->skipOptimizeIndexes = $skipOptimizeIndexes;
    }
}
