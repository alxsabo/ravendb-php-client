<?php

namespace RavenDB\Documents\Session;

use Closure;
use RavenDB\Documents\Queries\ProjectionBehavior;
use RavenDB\Documents\Queries\Timings\QueryTimings;
use RavenDB\Documents\Session\Operations\QueryOperation;
use RavenDB\Type\Duration;

interface DocumentQueryCustomizationInterface
{
    /**
     * Get the raw query operation that will be sent to the server
     * @return QueryOperation Query operation
     */
    public function getQueryOperation(): QueryOperation;

    /**
     * Get current Query
     * @return AbstractDocumentQuery
     */
    public function getQuery(): AbstractDocumentQuery;

    /**
     * Allow you to modify the index query before it is executed
     *
     * @param Closure $action action to call
     * @return DocumentQueryCustomizationInterface customization object
     */
    public function addBeforeQueryExecutedListener(Closure $action): DocumentQueryCustomizationInterface;

    /**
     * Allow you to modify the index query before it is executed
     * @param Closure $action action to call
     * @return DocumentQueryCustomizationInterface customization object
     */
    public function removeBeforeQueryExecutedListener(Closure $action): DocumentQueryCustomizationInterface;

    /**
     * Callback to get the results of the query
     * @param Closure $action action to call
     * @return DocumentQueryCustomizationInterface customization object
     */
    public function addAfterQueryExecutedListener(Closure $action): DocumentQueryCustomizationInterface;

    /**
     * Callback to get the results of the query
     * @param Closure $action action to call
     * @return DocumentQueryCustomizationInterface customization object
     */
    public function removeAfterQueryExecutedListener(Closure $action): DocumentQueryCustomizationInterface;


    /**
     * Callback to get the raw objects streamed by the query
     * @param Closure $action action to call
     * @return DocumentQueryCustomizationInterface customization object
     */
//    IDocumentQueryCustomization addAfterStreamExecutedCallback(Consumer<ObjectNode> action);

    /**
     * Callback to get the raw objects streamed by the query
     * @param Closure $action action to call
     * @return DocumentQueryCustomizationInterface customization object
     */
//    IDocumentQueryCustomization removeAfterStreamExecutedCallback(Consumer<ObjectNode> action);

    /**
     * Disables caching for query results.
     * @return DocumentQueryCustomizationInterface customization object
     */
    function noCaching(): DocumentQueryCustomizationInterface;

    /**
     * Disables tracking for queried entities by Raven's Unit of Work.
     * Usage of this option will prevent holding query results in memory.
     * @return DocumentQueryCustomizationInterface customization object
     */
    function noTracking(): DocumentQueryCustomizationInterface;

    /**
     * Usage
     * - randomOrdering()
     *     Disables tracking for queried entities by Raven's Unit of Work.
     *     Usage of this option will prevent holding query results in memory.
     *
     * - randomOrdering($seed)
     *     Order the search results randomly using the specified seed
     *     this is useful if you want to have repeatable random queries
     *
     * @param ?string $seed Random seed
     * @return DocumentQueryCustomizationInterface customization object
     */
    public function randomOrdering(?string $seed = null): DocumentQueryCustomizationInterface;

    //TBD 4.1 IDocumentQueryCustomization CustomSortUsing(string typeName);
    //TBD 4.1 IDocumentQueryCustomization CustomSortUsing(string typeName, bool descending);

    public function timings(QueryTimings &$timingsReference): DocumentQueryCustomizationInterface;

    /**
     * Instruct the query to wait for non stale results.
     * This shouldn't be used outside of unit tests unless you are well aware of the implications
     * @param ?Duration $waitTimeout Maximum time to wait for index query results to become non-stale before exception is thrown. Default: 15 seconds.
     * @return DocumentQueryCustomizationInterface customization object
     */
    public function waitForNonStaleResults(?Duration $waitTimeout = null): DocumentQueryCustomizationInterface;

    public function projection(ProjectionBehavior $projectionBehavior): DocumentQueryCustomizationInterface;
}
