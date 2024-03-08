<?php

namespace RavenDB\Documents\Session;

use Closure;
use RavenDB\Documents\Queries\Explanation\ExplanationOptions;
use RavenDB\Documents\Queries\Explanation\Explanations;
use RavenDB\Documents\Queries\Facets\AggregationDocumentQueryInterface;
use RavenDB\Documents\Queries\Facets\FacetBase;
use RavenDB\Documents\Queries\GroupBy;
use RavenDB\Documents\Queries\Highlighting\HighlightingOptions;
use RavenDB\Documents\Queries\Highlighting\Highlightings;
use RavenDB\Documents\Queries\MoreLikeThis\MoreLikeThisBase;
use RavenDB\Documents\Queries\QueryOperator;
use RavenDB\Documents\Queries\QueryResult;
use RavenDB\Documents\Queries\SearchOperator;
use RavenDB\Documents\Queries\Suggestions\SuggestionBase;
use RavenDB\Documents\Queries\Suggestions\SuggestionDocumentQueryInterface;
use RavenDB\Documents\Queries\Timings\QueryTimings;
use RavenDB\Type\Collection;
use RavenDB\Type\Duration;

interface DocumentQueryInterface
    extends DocumentQueryBaseInterface, DocumentQueryBaseSingleInterface, EnumerableQueryInterface
{
    function getIndexName(): ?string;

    function getQueryClass(): ?string;

    /**
     * Whether we should apply distinct operation to the query on the server side
     *
     * @return bool true if server should return distinct results
     */
    function isDistinct(): bool;

    /**
     * Returns the query result. Accessing this property for the first time will execute the query.
     *
     * @return QueryResult query result
     */
    function getQueryResult(): QueryResult;

    /**
     * selectFields(?string $projectionClass)
     * selectFields(?string $projectionClass, ?string ...$fields)
     * selectFields(?string $projectionClass, QueryData $queryData)
     * selectFields(?string $projectionClass, ProjectionBehavior $projectionBehavior)
     * selectFields(?string $projectionClass, ProjectionBehavior $projectionBehavior, ?string ...$fields)
     *
     * @param string|null  $projectionClass
     * @param mixed ...$params
     *
     * @return DocumentQueryInterface
     */
    public function selectFields(?string $projectionClass, ...$params): DocumentQueryInterface;

    /**
     * Selects a Time Series Aggregation based on
     * a time series query generated by an TimeSeriesQueryBuilderInterface.
     * @param ?string $className Result class
     * @param Closure $timeSeriesQuery query provider
     * @return DocumentQuery
     */
    public function selectTimeSeries(?string $className, Closure $timeSeriesQuery): DocumentQueryInterface;

    /**
     * Changes the return type of the query
     * @param string $resultClass class of result
     *
     * @return DocumentQueryInterface Document query
     */
    function ofType(string $resultClass): DocumentQueryInterface;

    /**
     * @param string|GroupBy $fieldName
     * @param string|GroupBy ...$fieldNames
     *
     * @return GroupByDocumentQueryInterface
     */
    public function groupBy($fieldName, ...$fieldNames): GroupByDocumentQueryInterface;

    public function moreLikeThis(null|MoreLikeThisBase|Closure $moreLikeThisOrBuilder): DocumentQueryInterface;

    /**
     * Filter allows querying on documents without the need for issuing indexes.
     * It is meant for exploratory queries or post query filtering.
     * Criteria are evaluated at query time so please use Filter wisely to avoid performance issues.
     * @param Closure $builder Builder of a Filter query
     * @param ?int $limit Limits the number of documents processed by Filter.
     * @return DocumentQueryInterface Document query
     */
    public function filter(Closure $builder, ?int $limit = null): DocumentQueryInterface;

    /**
     * @param Callable|FacetBase $builderOrFacets
     *
     * @return AggregationDocumentQueryInterface
     */
    public function aggregateBy(...$builderOrFacets): AggregationDocumentQueryInterface;

    public function aggregateUsing(?string $facetSetupDocumentId): AggregationDocumentQueryInterface;

    public function suggestUsing(null|SuggestionBase|Closure $suggestionOrBuilder): SuggestionDocumentQueryInterface;

    public function toString(bool $compatibilityMode = false): string;


    public function addBeforeQueryExecutedListener(Closure $action): DocumentQueryInterface;
    public function removeBeforeQueryExecutedListener(Closure $action): DocumentQueryInterface;
    public function addAfterQueryExecutedListener(Closure $action): DocumentQueryInterface;
    public function removeAfterQueryExecutedListener(Closure $action): DocumentQueryInterface;
    public function addAfterStreamExecutedListener(Closure $action): DocumentQueryInterface;
    public function removeAfterStreamExecutedListener(Closure $action): DocumentQueryInterface;

    function addParameter(string $name, $value): DocumentQueryInterface;

    function noCaching(): DocumentQueryInterface;
    function noTracking(): DocumentQueryInterface;
    function timings(QueryTimings &$timings): DocumentQueryInterface;
    function statistics(QueryStatistics &$stats): DocumentQueryInterface;
    function skip(int $count): DocumentQueryInterface;
    function take(int $count): DocumentQueryInterface;
    function waitForNonStaleResults(?Duration $waitTimeout = null): DocumentQueryInterface;

    function usingDefaultOperator(QueryOperator $queryOperator): DocumentQueryInterface;
    function not(): DocumentQueryInterface;
    public function andAlso(bool $wrapPreviousQueryClauses = false): DocumentQueryInterface;
    function closeSubclause(): DocumentQueryInterface;
    function containsAll(?string $fieldName, Collection|array $values): DocumentQueryInterface;
    function containsAny(?string $fieldName, Collection|array $values): DocumentQueryInterface;
    function negateNext(): DocumentQueryInterface;
    function openSubclause(): DocumentQueryInterface;
    public function orElse(): DocumentQueryInterface;
    public function search(string $fieldName, string $searchTerms, ?SearchOperator $operator = null): DocumentQueryInterface;
    function whereLucene(string $fieldName, string $whereClause, bool $exact = false): DocumentQueryInterface;
    function whereBetween(string $fieldName, $start, $end, bool $exact = false): DocumentQueryInterface;
    function whereEndsWith(string $fieldName, $value, bool $exact = false): DocumentQueryInterface;
    function whereEquals(string $fieldName, $value, bool $exact = false): DocumentQueryInterface;
    function whereEqualsWithParams(WhereParams $whereParams): DocumentQueryInterface;
    function whereNotEquals(string $fieldName, $value, bool $exact = false): DocumentQueryInterface;
    function whereNotEqualsWithParams(WhereParams $whereParams): DocumentQueryInterface;
    function whereGreaterThan(string $fieldName, $value, bool $exact = false): DocumentQueryInterface;
    function whereGreaterThanOrEqual(string $fieldName, $value, bool $exact = false): DocumentQueryInterface;
    function whereIn(string $fieldName, Collection|array $values, bool $exact = false): DocumentQueryInterface;
    function whereLessThan(string $fieldName, $value, bool $exact = false): DocumentQueryInterface;
    function whereLessThanOrEqual(string $fieldName, $value, bool $exact = false): DocumentQueryInterface;
    function whereStartsWith(string $fieldName, $value, bool $exact = false): DocumentQueryInterface;
    function whereExists(string $fieldName): DocumentQueryInterface;
    function whereRegex(?string $fieldName, ?string $pattern): FilterDocumentQueryBaseInterface;


    function addOrder(?string $fieldName, bool $descending, ?OrderingType $ordering = null): DocumentQueryInterface;
    function boost(float $boost): DocumentQueryInterface;
    function distinct(): DocumentQueryInterface;
    public function includeExplanations(?ExplanationOptions $options, Explanations &$explanations): DocumentQueryInterface;
    function fuzzy(float $fuzzy): DocumentQueryInterface;
    function highlight(?string $fieldName, int $fragmentLength, int $fragmentCount, ?HighlightingOptions $options , Highlightings &$highlightings): DocumentQueryInterface;
    function include($includes): DocumentQueryInterface;
    function intersect(): DocumentQueryInterface;
    function orderBy(string $field, $sorterNameOrOrdering = null): DocumentQueryInterface;
    function orderByDescending(string $field, $sorterNameOrOrdering = null): DocumentQueryInterface;
    function proximity(int $proximity): DocumentQueryInterface;
    function randomOrdering(?string $seed = null): DocumentQueryInterface;
}
