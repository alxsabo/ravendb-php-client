<?php

namespace RavenDB\Documents\Operations;

use RavenDB\Documents\Conventions\DocumentConventions;
use RavenDB\Exceptions\IllegalArgumentException;
use RavenDB\Http\RavenCommand;
use RavenDB\ServerWide\CompactSettings;
use RavenDB\ServerWide\Operations\ServerOperationInterface;

class CompactDatabaseOperation implements ServerOperationInterface
{
    private CompactSettings $compactSettings;

    public function __construct(?CompactSettings $compactSettings)
    {
        if ($compactSettings == null) {
            throw new IllegalArgumentException("CompactSettings cannot be null");
        }
        $this->compactSettings = $compactSettings;
    }

    public function getCommand(DocumentConventions $conventions): RavenCommand
    {
        return new CompactDatabaseCommand($conventions, $this->compactSettings);
    }
}
