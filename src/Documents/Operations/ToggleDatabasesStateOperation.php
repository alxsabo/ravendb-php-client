<?php

namespace RavenDB\Documents\Operations;

use RavenDB\Http\RavenCommand;
use RavenDB\Exceptions\IllegalArgumentException;
use RavenDB\Documents\Conventions\DocumentConventions;
use RavenDB\ServerWide\Operations\ServerOperationInterface;
use RavenDB\Type\StringArray;

class ToggleDatabasesStateOperation implements ServerOperationInterface
{
    private bool $disable = false;
    private ?ToggleDatabasesStateParameters $parameters = null;

    public function __construct(null|string|array|StringArray $databaseName, bool $disable = false)
    {
        if ($databaseName == null) {
            throw new IllegalArgumentException("DatabaseName cannot be null");
        }

        $this->disable = $disable;
        $this->parameters = new ToggleDatabasesStateParameters();
        $this->parameters->setDatabaseNames(is_string($databaseName) ? [ $databaseName ] : $databaseName);
    }

    public function getCommand(DocumentConventions $conventions): RavenCommand
    {
        return new ToggleDatabaseStateCommand($this->parameters, $this->disable);
    }
}
