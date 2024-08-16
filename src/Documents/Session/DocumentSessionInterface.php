<?php

namespace RavenDB\Documents\Session;

// @todo: implement this interface
use RavenDB\Documents\Queries\Query;
use RavenDB\Documents\Session\Loaders\LoaderWithIncludeInterface;
use RavenDB\Exceptions\Documents\Session\NonUniqueObjectException;
use RavenDB\Exceptions\IllegalArgumentException;
use RavenDB\Exceptions\IllegalStateException;
use RavenDB\Type\ObjectArray;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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
     * Usage
     *  - delete(?object $entity): void;
     *  - delete(?string $id): void;
     *  - delete(?string $id, ?string $expectedChangeVector): void;
     *
     * @param string|object|null $entity
     * @param ?string $expectedChangeVector
     *
     * @throws IllegalArgumentException
     * @throws IllegalStateException
     */
    public function delete(string|object|null $entity, ?string $expectedChangeVector = null): void;

    /**
     * Saves all the pending changes to the server.
     */
    public function saveChanges(): void;

    /**
     * Store entities inside the session object.
     *
     * Usage:
     *  - public function store(?object $entity): void;
     *  - public function store(?object $entity, ?string $id): void;
     *  - public function store(?object $entity, ?string $id, ?string $changeVector): void;
     *
     * @throws IllegalStateException|NonUniqueObjectException|ExceptionInterface
     */
    public function store(?object $entity, ?string $id = null, ?string $changeVector = null): void;

    /**
     * Begin a load while including the specified path.
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
     * Usage
     *
     *  - load(string $className, string $id): ?object
     *  - load(string $className, string $id, Closure $includes) ?Object;
     *
     *  - load(string $className, StringArray $ids): ObjectArray
     *  - load(string $className, StringArray $ids, Closure $includes): ObjectArray;
     *
     *  - load(string $className, array $ids): ObjectArray
     *  - load(string $className, array $ids, Closure $includes): ObjectArray;
     *
     *  - load(string $className, string $id1, string $id2, string $id3 ... ): ObjectArray
     *
     * @param ?string $className Object class
     * @param mixed $params Identifier of a entity that will be loaded.
     *
     * @return mixed null|object|ObjectArray Loaded entity or entities
     */
    public function load(?string $className, ...$params): mixed;

    /**
     * @param string $className
     * @param Query|null|string $collectionOrIndexName
     *
     * @return DocumentQueryInterface
     */
    public function query(string $className, Query|null|string $collectionOrIndexName = null): DocumentQueryInterface;

    /**
     * @param string $className
     * @param string $query
     *
     * @return RawDocumentQueryInterface
     */
    public function rawQuery(string $className, string $query): RawDocumentQueryInterface;

    public function countersFor(string|object $idOrEntity): SessionDocumentCountersInterface;

    public function timeSeriesFor(string|object|null $idOrEntity, ?string $name): SessionDocumentTimeSeriesInterface;

    public function typedTimeSeriesFor(string $className, string|object|null $idOrEntity, ?string $name = null): SessionDocumentTypedTimeSeriesInterface;

    public function timeSeriesRollupFor(string $className, string|object|null $idOrEntity, ?string $policy, ?string $raw = null): SessionDocumentRollupTypedTimeSeriesInterface;

    public function incrementalTimeSeriesFor(string|object|null $idOrEntity, ?string $name): SessionDocumentTimeSeriesInterface;

    public function incrementalTypedTimeSeriesFor(string $className, string|object|null $idOrEntity, ?string $name = null): SessionDocumentTypedTimeSeriesInterface;

    public function close(): void;
}
