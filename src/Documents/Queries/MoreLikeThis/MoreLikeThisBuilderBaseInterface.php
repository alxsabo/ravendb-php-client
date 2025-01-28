<?php

namespace RavenDB\Documents\Queries\MoreLikeThis;

use Closure;

interface MoreLikeThisBuilderBaseInterface
{
//    function usingAnyDocument(): MoreLikeThisOperationsInterface;
    /**
     * Usage:
     *   - usingDocument();
     *   - usingDocument(string $documentJson);
     *   - usingDocument(function($builder) {...});
     *
     * @param string|Closure|null $documentJsonOrBuilder
     * @return MoreLikeThisOperationsInterface
     */
    function usingDocument(null|string|Closure $documentJsonOrBuilder): MoreLikeThisOperationsInterface;

    // same as calling usingDocument(string $documentJson)
    function usingDocumentWithJson(?string $documentJson): MoreLikeThisOperationsInterface;

    // same as calling usingDocument(function($builder) {...});
    function usingDocumentWithBuilder(?Closure $builder): MoreLikeThisOperationsInterface;
}
