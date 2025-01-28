<?php

namespace RavenDB\Documents\Operations;

use Closure;
use RavenDB\Documents\Conventions\DocumentConventions;
use RavenDB\Exceptions\ExceptionDispatcher;
use RavenDB\Exceptions\ExceptionSchema;
use RavenDB\Exceptions\TimeoutException;
use RavenDB\Extensions\JsonExtensions;
use RavenDB\Http\RavenCommand;
use RavenDB\Http\RequestExecutor;
use RavenDB\Primitives\OperationCancelledException;
use RavenDB\Type\Duration;
use RavenDB\Utils\Stopwatch;

class Operation
{
    private RequestExecutor $requestExecutor;
    private DocumentConventions $conventions;
    private int $id;
    private ?string $nodeTag;

    public function getId(): int
    {
        return $this->id;
    }

    public function __construct(
        RequestExecutor $requestExecutor,
        DocumentConventions $conventions,
        int $id,
        ?string $nodeTag = null
    )
    {
        $this->requestExecutor = $requestExecutor;
        $this->conventions = $conventions;
        $this->id = $id;
        $this->nodeTag = $nodeTag;
    }

    private function fetchOperationsStatus(): array
    {
        $command = $this->getOperationStateCommand($this->conventions, $this->id, $this->nodeTag);
        $this->requestExecutor->execute($command);

        return $command->getResult();
    }

    protected function getOperationStateCommand(DocumentConventions $conventions, int $id, ?string $nodeTag = null): RavenCommand
    {
        return new GetOperationStateCommand($id, $nodeTag);
    }

    public function getNodeTag(): string
    {
        return $this->nodeTag;
    }

    public function setNodeTag(string $nodeTag): void
    {
        $this->nodeTag = $nodeTag;
    }

    /**
     * Wait for operation completion.
     *
     * It throws TimoutException if $duration is set and operation execution time elapses duration interval.
     *
     * Usage:
     *   - waitForCompletion(): void;               // It will wait until operation is finished
     *   - waitForCompletion(Duration $duration);   // It will wait for given duration
     *   - waitForCompletion(int $seconds);         // It will wait for given seconds
     *
     * @param Duration|int|null $duration
     *
     * @return mixed Returns operation result on Completed status
     */
    public function waitForCompletion(Duration|int|null $duration = null): mixed
    {
        $stopwatch = Stopwatch::createStarted();

        if (is_int($duration)) {
            $duration = Duration::ofSeconds($duration);
        }

        while (true) {
            $status = $this->fetchOperationsStatus();

            $operationStatus = $status['Status'];

            switch ($operationStatus) {
                case 'Completed':
                    return $this->extractResult($status['Result']);
                case 'Canceled':
                    throw new OperationCancelledException();
                case 'Faulted':
                    $result = $status['Result'];

                    /** @var OperationExceptionResult $exceptionResult */
                    $exceptionResult = JsonExtensions::getDefaultMapper()->denormalize($result, OperationExceptionResult::class);
                    $schema = new ExceptionSchema();

                    $schema->setUrl($this->requestExecutor->getUrl());
                    $schema->setError($exceptionResult->getError());
                    $schema->setMessage($exceptionResult->getMessage());
                    $schema->setType($exceptionResult->getType());

                    $exception = ExceptionDispatcher::get($schema, $exceptionResult->getStatusCode());
                    throw new $exception;
            }

            if ($duration) {
                if ($stopwatch->elapsedInMillis() > $duration->toMillis()) {
                    throw new TimeoutException("Wait for completion time expired.");
                }
            }

            usleep(500000);
        }
    }

    protected function extractResult($data): ?BulkOperationResult
    {
        if (array_key_exists('$type', $data) &&  $data['$type'] != 'Raven.Client.Documents.Operations.BulkOperationResult, Raven.Client') {
            return null;
        }

        $entityMapper = $this->conventions->getEntityMapper();
        return $entityMapper->denormalize($data, BulkOperationResult::class);
    }

}
