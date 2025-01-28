<?php

namespace RavenDB\Documents\Operations;

use RavenDB\Type\StringArray;

use Symfony\Component\Serializer\Annotation\SerializedName;

class ToggleDatabasesStateParameters
{
    /** @SerializedName ("DatabaseNames") */
    private StringArray $databaseNames;

    /**
     * @param StringArray|array $databaseNames
     */
    public function __construct(StringArray|array $databaseNames = [])
    {
        $this->setDatabaseNames($databaseNames);
    }

    public function getDatabaseNames(): StringArray
    {
        return $this->databaseNames;
    }

    /**
     * @param StringArray|array $databaseNames
     */
    public function setDatabaseNames(StringArray|array $databaseNames): void
    {
        if (is_array($databaseNames)) {
            $databaseNames = StringArray::fromArray($databaseNames);
        }
        $this->databaseNames = $databaseNames;
    }
}
