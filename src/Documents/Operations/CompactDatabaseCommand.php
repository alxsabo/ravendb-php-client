<?php

namespace RavenDB\Documents\Operations;

use RavenDB\Documents\Conventions\DocumentConventions;
use RavenDB\Exceptions\IllegalArgumentException;
use RavenDB\Http\HttpRequest;
use RavenDB\Http\HttpRequestInterface;
use RavenDB\Http\RavenCommand;
use RavenDB\Http\ServerNode;
use RavenDB\ServerWide\CompactSettings;

class CompactDatabaseCommand extends RavenCommand
{
    private ?CompactSettings $compactSettings = null;

    public function __construct(?DocumentConventions $conventions, ?CompactSettings $compactSettings)
    {
        parent::__construct(OperationIdResult::class);

        if ($conventions == null) {
            throw new IllegalArgumentException("Conventions cannot be null");
        }

        if ($compactSettings == null) {
            throw new IllegalArgumentException("CompactSettings cannot be null");
        }

        $this->compactSettings = $compactSettings;
    }

    public function createUrl(ServerNode $serverNode): string
    {
        return $serverNode->getUrl() . "/admin/compact";

    }

    public function createRequest(ServerNode $serverNode): HttpRequestInterface
    {
        $options = [
            'json' => $this->getMapper()->normalize($this->compactSettings),
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        return new HttpRequest($this->createUrl($serverNode), HttpRequest::POST, $options);
    }
}
