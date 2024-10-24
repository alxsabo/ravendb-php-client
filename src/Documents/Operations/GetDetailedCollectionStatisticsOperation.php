<?php

namespace RavenDB\Documents\Operations;

use RavenDB\Documents\Conventions\DocumentConventions;
use RavenDB\Http\RavenCommand;

class GetDetailedCollectionStatisticsOperation
{
    public function getCommand(DocumentConventions $conventions): RavenCommand
    {
        return new GetDetailedCollectionStatisticsCommand();
    }
}
