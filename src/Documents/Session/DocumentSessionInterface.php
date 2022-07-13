<?php

namespace RavenDB\Documents\Session;

// @todo: implement this interface
use RavenDB\Documents\Queries\Query;
use RavenDB\Documents\Session\Loaders\LoaderWithIncludeInterface;
use RavenDB\Type\ObjectArray;
use RavenDB\Type\StringArray;

interface DocumentSessionInterface
{

    /**
     * Get the accessor for advanced operations
     *
     * Those operations are rarely needed, and have been moved to a separate
     * property to avoid cluttering the API
     * @return AdvancedSessionOperationsInterface Advance session operations
     */
    public function advanced(): AdvancedSessionOperationsInterface;

    /**
     * Marks the specified entity for deletion. The entity will be deleted when IDocumentSession.saveChanges is called.
     *
     * WARNING: This method when used with string entityId will not call beforeDelete listener!
     *
     * @param string|object|null $entity instance of entity to delete
     * @param ?string $expectedChangeVector Expected change vector of a document to delete.
     */
    public function delete($entity, ?string $expectedChangeVector = null): void;

    /**
     * Saves all the pending changes to the server.
     */
    public function saveChanges(): void;

//    /**
//     * Stores entity in session with given id and forces concurrency check with given change-vector.
//     * @param entity Entity to store
//     * @param changeVector Change vector
//     * @param id Document id
//     */
//    void store(Object entity, String changeVector, String id);
//
//    /**
//     * Stores entity in session, extracts Id from entity using Conventions or generates new one if it is not available.
//     * Forces concurrency check if the Id is not available during extraction.
//     * @param entity Entity to store
//     */
//    void store(Object entity);
//
//    /**
//     * Stores the specified dynamic entity, under the specified id.
//     * @param entity entity to store
//     * @param id Id to store this entity under. If other entity exists with the same id it will be overwritten.
//     */
//    void store(Object entity, String id);

    public function store(object $entity, ?string $id = null): void;

    /**
     * Begin a load while including the specified path
     * Path in documents in which server should look for a 'referenced' documents.
     *
     * @param ?string $path Path to include
     * @return LoaderWithIncludeInterface Loader with includes
     */
    function include(?string $path): LoaderWithIncludeInterface;

    //TBD expr another includes here?

    /**
     * Loads the specified entity with the specified id.
     *
     * load(string $className, string $id): ?object
     * load(string $className, string $id, Closure $includes) ?Object;
     *
     * load(string $className, StringArray $ids): ObjectArray
     * load(string $className, StringArray $ids, Closure $includes): ObjectArray;
     *
     * load(string $className, array $ids): ObjectArray
     * load(string $className, array $ids, Closure $includes): ObjectArray;
     *
     * load(string $className, string $id1, string $id2, string $id3 ... ): ObjectArray
     *
     * @param string $className Object class
     * @param mixed $params Identifier of a entity that will be loaded.
     *
     * @return null|object|ObjectArray Loaded entity or entities
     */
    public function load(string $className, ...$params);

    /**
     * @param string $className
     * @param Query|null|string $collectionOrIndexName
     *
     * @return DocumentQueryInterface
     */
    public function query(string $className, $collectionOrIndexName = null): DocumentQueryInterface;

    /**
     * @param string $className
     * @param string $query
     *
     * @return RawDocumentQueryInterface
     */
    public function rawQuery(string $className, string $query): RawDocumentQueryInterface;

//    ISessionDocumentCounters countersFor(String documentId);
//
//    ISessionDocumentCounters countersFor(Object entity);
//
//    ISessionDocumentTimeSeries timeSeriesFor(String documentId, String name);
//
//    ISessionDocumentTimeSeries timeSeriesFor(Object entity, String name);
//
//    <T> ISessionDocumentTypedTimeSeries<T> timeSeriesFor(Class<T> clazz, String documentId);
//
//    <T> ISessionDocumentTypedTimeSeries<T> timeSeriesFor(Class<T> clazz, String documentId, String name);
//
//    <T> ISessionDocumentTypedTimeSeries<T> timeSeriesFor(Class<T> clazz, Object entity);
//
//    <T> ISessionDocumentTypedTimeSeries<T> timeSeriesFor(Class<T> clazz, Object entity, String name);
//
//    <T> ISessionDocumentRollupTypedTimeSeries<T> timeSeriesRollupFor(Class<T> clazz, Object entity, String policy);
//
//    <T> ISessionDocumentRollupTypedTimeSeries<T> timeSeriesRollupFor(Class<T> clazz, Object entity, String policy, String raw);
//
//    <T> ISessionDocumentRollupTypedTimeSeries<T> timeSeriesRollupFor(Class<T> clazz, String documentId, String policy);
//
//    <T> ISessionDocumentRollupTypedTimeSeries<T> timeSeriesRollupFor(Class<T> clazz, String documentId, String policy, String raw);

    public function close(): void;
}
