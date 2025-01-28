<?php

namespace RavenDB\Documents\Operations\Indexes;

use RavenDB\Documents\Conventions\DocumentConventions;
use RavenDB\Documents\Indexes\IndexDefinition;
use RavenDB\Documents\Indexes\IndexDefinitionArray;
use RavenDB\Documents\Operations\MaintenanceOperationInterface;
use RavenDB\Exceptions\IllegalArgumentException;
use RavenDB\Http\RavenCommand;

class PutIndexesOperation implements MaintenanceOperationInterface
{
    private ?IndexDefinitionArray $indexToAdd = null;
    private bool $allJavaScriptIndexes = false;

    /**
     * @param IndexDefinition|IndexDefinitionArray|array ...$indexToAdd
     */
    public function __construct(...$indexToAdd) {
        if (empty($indexToAdd)) {
            throw new IllegalArgumentException("indexToAdd cannot be null");
        }

        if (is_array($indexToAdd[0])) {
            $this->indexToAdd = IndexDefinitionArray::fromArray($indexToAdd[0]);
            return;
        }

        if ($indexToAdd[0] instanceof IndexDefinitionArray) {
            $this->indexToAdd = $indexToAdd[0];
            return;
        }

        $this->indexToAdd = IndexDefinitionArray::fromArray($indexToAdd);
    }

    public function getCommand(DocumentConventions $conventions): RavenCommand
    {
        return new PutIndexesCommand($conventions, $this->indexToAdd);
    }
}
