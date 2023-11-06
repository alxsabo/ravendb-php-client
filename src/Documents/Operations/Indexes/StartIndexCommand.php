<?php

namespace RavenDB\Documents\Operations\Indexes;

use RavenDB\Exceptions\IllegalArgumentException;
use RavenDB\Http\HttpRequest;
use RavenDB\Http\HttpRequestInterface;
use RavenDB\Http\ServerNode;
use RavenDB\Http\VoidRavenCommand;
use RavenDB\Utils\UrlUtils;

class StartIndexCommand extends VoidRavenCommand
{
    private ?string $indexName = null;

    public function __construct(?string $indexName)
    {
        parent::__construct();

        if ($indexName == null) {
            throw new IllegalArgumentException("Index name cannot be null");
        }

        $this->indexName = $indexName;
    }
    public function createUrl(ServerNode $serverNode): string
    {
        return $serverNode->getUrl() . "/databases/" . $serverNode->getDatabase() . "/admin/indexes/start?name=" . UrlUtils::escapeDataString($this->indexName);
    }

    public function createRequest(ServerNode $serverNode): HttpRequestInterface
    {
        return new HttpRequest($this->createUrl($serverNode), HttpRequest::POST);
    }
}
