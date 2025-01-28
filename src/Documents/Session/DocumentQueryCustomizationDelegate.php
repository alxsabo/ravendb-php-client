<?php

namespace RavenDB\Documents\Session;

use Closure;
use RavenDB\Documents\Queries\ProjectionBehavior;
use RavenDB\Documents\Queries\Timings\QueryTimings;
use RavenDB\Documents\Session\Operations\QueryOperation;
use RavenDB\Type\Duration;

class DocumentQueryCustomizationDelegate implements DocumentQueryCustomizationInterface
{
    private AbstractDocumentQuery $query;

    public function __construct(AbstractDocumentQuery $query)
    {
        $this->query = $query;
    }

    public function getQuery(): AbstractDocumentQuery
    {
        return $this->query;
    }

    public function getQueryOperation(): QueryOperation
    {
        return $this->query->getQueryOperation();
    }

    public function addBeforeQueryExecutedListener(Closure $action): DocumentQueryCustomizationInterface
    {
        $this->query->_addBeforeQueryExecutedListener($action);
        return $this;
    }

    public function removeBeforeQueryExecutedListener(Closure $action): DocumentQueryCustomizationInterface
    {
        $this->query->_removeBeforeQueryExecutedListener($action);
        return $this;
    }


    public function addAfterQueryExecutedListener(Closure $action): DocumentQueryCustomizationInterface
    {
        $this->query->_addAfterQueryExecutedListener($action);
        return $this;
    }

    public function removeAfterQueryExecutedListener(Closure $action): DocumentQueryCustomizationInterface
    {
        $this->query->_removeAfterQueryExecutedListener($action);
        return $this;
    }

//    public function addAfterStreamExecutedCallback(Closure $action): DocumentQueryCustomizationInterface
//    {
//        $this->query->_addAfterStreamExecutedListener($action);
//        return $this;
//    }

//    public function removeAfterStreamExecutedCallback(Closure $action): DocumentQueryCustomizationInterface
//    {
//        $this->query->_removeAfterStreamExecutedListener($action);
//        return $this;
//    }

    public function noCaching(): DocumentQueryCustomizationInterface
    {
        $this->query->_noCaching();
        return $this;
    }

    public function noTracking(): DocumentQueryCustomizationInterface
    {
        $this->query->_noTracking();
        return $this;
    }

    public function timings(QueryTimings &$timingsReference): DocumentQueryCustomizationInterface
    {
        $this->query->_includeTimings($timingsReference);
        return $this;
    }

    public function randomOrdering(?string $seed = null): DocumentQueryCustomizationInterface
    {
        $this->query->_randomOrdering($seed);
        return $this;
    }

    public function waitForNonStaleResults(?Duration $waitTimeout = null): DocumentQueryCustomizationInterface
    {
        $this->query->_waitForNonStaleResults($waitTimeout);
        return $this;
    }

    public function projection(ProjectionBehavior $projectionBehavior): DocumentQueryCustomizationInterface
    {
        $this->query->_projection($projectionBehavior);
        return $this;
    }
}
